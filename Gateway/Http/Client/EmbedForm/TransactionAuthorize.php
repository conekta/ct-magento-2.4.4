<?php

namespace Conekta\Payments\Gateway\Http\Client\EmbedForm;

use Conekta\Order as ConektaOrder;
use Conekta\Payments\Api\Data\ConektaSalesOrderInterface;
use Conekta\Payments\Gateway\Http\Util\HttpUtil;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\Payments\Model\ConektaSalesOrderFactory;
use Conekta\Payments\Model\Ui\EmbedForm\ConfigProvider;
use Exception;
use Magento\Payment\Gateway\Http\{ClientInterface, TransferInterface};
use Magento\Payment\Model\Method\Logger;

class TransactionAuthorize implements ClientInterface
{
    public const SUCCESS = 1;
    public const FAILURE = 0;

    /**
     * @var array
     */
    private array $results = [
        self::SUCCESS,
        self::FAILURE
    ];

    /**
     * @param Logger $logger
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     * @param ConektaOrder $conektaOrder
     * @param HttpUtil $httpUtil
     * @param ConektaSalesOrderFactory $conektaSalesOrderFactory
     * @throws \Magento\Framework\Validator\Exception
     */
    public function __construct(
        private Logger $logger,
        protected ConektaHelper $conektaHelper,
        private ConektaLogger $conektaLogger,
        protected ConektaOrder $conektaOrder,
        protected HttpUtil $httpUtil,
        protected ConektaSalesOrderFactory $conektaSalesOrderFactory
    ) {
        $config = [
            'locale' => 'es'
        ];

        $this->conektaLogger->info('HTTP Client TransactionCapture :: __construct');
        $this->httpUtil->setupConektaClient($config);
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $request = $transferObject->getBody();
        $this->conektaLogger->info('HTTP Client TransactionCapture :: placeRequest', $request);

        $txnId = $request['txn_id'];

        $this->conektaSalesOrderFactory
                    ->create()
                    ->setData([
                        ConektaSalesOrderInterface::CONEKTA_ORDER_ID   => $request['order_id'],
                        ConektaSalesOrderInterface::INCREMENT_ORDER_ID => $request['metadata']['order_id']
                    ])
                    ->save();

        $paymentMethod = $request['payment_method_details']['payment_method']['type'];
        $response = [];
        //If is offline payment, added extra info needed
        if ($paymentMethod == ConfigProvider::PAYMENT_METHOD_OXXO
            || $paymentMethod == ConfigProvider::PAYMENT_METHOD_SPEI
        ) {
            $response['offline_info'] = [];

            try {
                $conektaOrder = $this->conektaOrder->find($request['order_id']);
                $charge = $conektaOrder->charges[0];
                $txnId = $charge->id;
                $response['offline_info'] = [
                    'type' => $charge->payment_method->type,
                    'data' => [
                        'expires_at' => $charge->payment_method->expires_at
                    ]
                ];

                if ($paymentMethod == ConfigProvider::PAYMENT_METHOD_OXXO) {
                    $response['offline_info']['data']['barcode_url'] = $charge->payment_method->barcode_url;
                    $response['offline_info']['data']['reference'] = $charge->payment_method->reference;
                } else {
                    $response['offline_info']['data']['clabe'] = $charge->payment_method->clabe;
                    $response['offline_info']['data']['bank_name'] = $charge->payment_method->bank;
                }
            } catch (Exception $e) {
                $this->conektaLogger->error(
                    'EmbedForm :: HTTP Client TransactionCapture :: cannot get offline info. ',
                    [ 'exception' => $e ]
                );
            }
        }

        $response = $this->generateResponseForCode(
            $response,
            1,
            $txnId,
            $request['order_id']
        );
        $response['error_code'] = '';
        $response['payment_method_details'] = $request['payment_method_details'];

        $this->conektaLogger->info(
            'HTTP Client TransactionCapture Iframe Payment :: placeRequest',
            [
                'request'  => $request,
                'response' => $response
            ]
        );

        return $response;
    }

    /**
     * @param $response
     * @param $resultCode
     * @param $txn_id
     * @param $ord_id
     * @return array
     */
    protected function generateResponseForCode($response, $resultCode, $txn_id, $ord_id): array
    {
        $this->conektaLogger->info('HTTP Client TransactionCapture :: generateResponseForCode');

        if (empty($txn_id)) {
            $txn_id = $this->generateTxnId();
        }
        return array_merge(
            $response,
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID'      => $txn_id,
                'ORD_ID'      => $ord_id
            ]
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function generateTxnId(): string
    {
        $this->conektaLogger->info('HTTP Client TransactionCapture :: generateTxnId');

        return sha1(random_int(0, 1000));
    }
}
