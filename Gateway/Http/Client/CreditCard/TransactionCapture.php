<?php

namespace Conekta\Payments\Gateway\Http\Client\CreditCard;

use Conekta\Order as ConektaOrder;
use Conekta\Payments\Api\Data\ConektaSalesOrderInterface;
use Conekta\Payments\Gateway\Http\Util\HttpUtil;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\Payments\Model\ConektaSalesOrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Exception;
use Magento\Payment\Gateway\Http\{ClientInterface, TransferInterface};
use Magento\Payment\Model\Method\Logger;

class TransactionCapture implements ClientInterface
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
     * @param ConektaHelper $_conektaHelper
     * @param ConektaLogger $conektaLogger
     * @param ConektaOrder $conektaOrder
     * @param HttpUtil $_httpUtil
     * @param ConektaSalesOrderFactory $conektaSalesOrderFactory
     * @throws Exception
     */
    public function __construct(
        private Logger $logger,
        protected ConektaHelper $_conektaHelper,
        private ConektaLogger $conektaLogger,
        private ConektaOrder $conektaOrder,
        protected HttpUtil $_httpUtil,
        protected ConektaSalesOrderFactory $conektaSalesOrderFactory
    ) {
        $config = [
            'locale' => 'es'
        ];

        $this->conektaLogger->info('HTTP Client TransactionCapture :: __construct');
        $this->_httpUtil->setupConektaClient($config);
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $this->conektaLogger->info('HTTP Client TransactionCapture :: placeRequest');
        $request = $transferObject->getBody();

        $orderParams['currency'] = $request['CURRENCY'];
        $orderParams['line_items'] = $request['line_items'];
        $orderParams['tax_lines'] = $request['tax_lines'];
        $orderParams['customer_info'] = $request['customer_info'];
        $orderParams['discount_lines'] = $request['discount_lines'];
        if (! empty($request['shipping_lines'])) {
            $orderParams['shipping_lines'] = $request['shipping_lines'];
        }
        if (! empty($request['shipping_contact'])) {
            $orderParams['shipping_contact'] = $request['shipping_contact'];
        }
        $orderParams['metadata'] = $request['metadata'];
        $chargeParams = $request['payment_method_details'];

        $txn_id = '';
        $ord_id = '';
        $error_code = '';

        try {
            $newOrder = $this->conektaOrder->create($orderParams);
            $newCharge = $newOrder->createCharge($chargeParams);
            if (isset($newCharge->id) || ! empty($newCharge->id)) {
                $result_code = 1;
                $txn_id = $newCharge->id;
                $ord_id = $newOrder->id;

                $this->conektaSalesOrderFactory
                        ->create()
                        ->setData([
                            ConektaSalesOrderInterface::CONEKTA_ORDER_ID   => $ord_id,
                            ConektaSalesOrderInterface::INCREMENT_ORDER_ID => $request['metadata']['order_id']
                        ])
                        ->save();
            } else {
                $result_code = 666;
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                [
                    'request'  => $request,
                    'response' => $e->getMessage()
                ]
            );
            $this->conektaLogger->info(
                'HTTP Client TransactionCapture :: placeRequest: Payment capturing error ' . $e->getMessage()
            );

            $error_code = $e->getMessage();
            $result_code = 666;
            throw new LocalizedException(__($error_code));
        }

        $response = $this->generateResponseForCode(
            $result_code,
            $txn_id,
            $ord_id
        );
        $response['error_code'] = $error_code;
        $response['payment_method_details'] = $request['payment_method_details'];

        $this->logger->debug(
            [
                'request'  => $request,
                'response' => $response
            ]
        );

        $this->conektaLogger->info(
            'HTTP Client TransactionCapture :: placeRequest',
            [
                'request'  => $request,
                'response' => $response
            ]
        );

        return $response;
    }

    /**
     * @param $resultCode
     * @param $txn_id
     * @param $ord_id
     * @return array
     */
    protected function generateResponseForCode($resultCode, $txn_id, $ord_id): array
    {
        if (empty($txn_id)) {
            $txn_id = $this->generateTxnId();
        }

        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID'      => $txn_id,
                'ORD_ID'      => $ord_id
            ]
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function generateTxnId(): string
    {
        return sha1(random_int(0, 1000));
    }
}
