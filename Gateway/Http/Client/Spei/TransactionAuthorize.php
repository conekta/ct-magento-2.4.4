<?php

namespace Conekta\Payments\Gateway\Http\Client\Spei;

use Conekta\Order as ConektaOrder;
use Conekta\Payments\Api\Data\ConektaSalesOrderInterface;
use Conekta\Payments\Gateway\Http\Util\HttpUtil;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\Payments\Model\ConektaSalesOrderFactory;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Validator\Exception;
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
     * @throws Exception
     */
    public function __construct(
        private Logger                     $logger,
        protected ConektaHelper            $conektaHelper,
        private ConektaLogger              $conektaLogger,
        private ConektaOrder               $conektaOrder,
        protected HttpUtil                 $httpUtil,
        protected ConektaSalesOrderFactory $conektaSalesOrderFactory
    ) {
        $config = [
            'locale' => 'es'
        ];

        $this->conektaLogger->info('HTTP Client Spei TransactionAuthorize :: __construct');
        $this->httpUtil->setupConektaClient($config);
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws ValidatorException
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $this->conektaLogger->info('HTTP Client Spei TransactionAuthorize :: placeRequest');
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

        $result_code = '';
        $txn_id = '';
        $ord_id = '';
        $error_code = '';

        try {
            $conektaOrder = $this->conektaOrder->create($orderParams);

            $charge = $conektaOrder->createCharge($chargeParams);

            if (isset($charge->id, $conektaOrder->id)) {
                $result_code = 1;
                $txn_id = $charge->id;
                $ord_id = $conektaOrder->id;

                $this->conektaSalesOrderFactory
                    ->create()
                    ->setData([
                        ConektaSalesOrderInterface::CONEKTA_ORDER_ID   => $ord_id,
                        ConektaSalesOrderInterface::INCREMENT_ORDER_ID => $orderParams['metadata']['order_id']
                    ])
                    ->save();
            } else {
                $result_code = 666;
            }
        } catch (ValidatorException $e) {
            $error_code = $e->getMessage();
            $result_code = 666;
            $this->logger->error(__('[Conekta]: Payment capturing error.'));
            $this->conektaHelper->deleteSavedCard($orderParams, $chargeParams);
            $this->logger->debug(
                [
                    'request'  => $request,
                    'response' => $e->getMessage()
                ]
            );
            $this->conektaLogger->info(
                'HTTP Client Spei TransactionAuthorize :: placeRequest: Payment authorize error ' . $e->getMessage()
            );
            throw new ValidatorException(__($e->getMessage()));
        }

        $response = $this->generateResponseForCode(
            $result_code,
            $txn_id,
            $ord_id
        );

        $response['offline_info'] = [
            'type' => $charge->payment_method->type,
            'data' => [
                'clabe'      => $charge->payment_method->clabe,
                'bank_name'  => $charge->payment_method->bank,
                'expires_at' => $charge->payment_method->expires_at
            ]
        ];

        $response['error_code'] = $error_code;

        $this->logger->debug(
            [
                'request'  => $request,
                'response' => $response
            ]
        );

        $this->conektaLogger->info(
            'HTTP Client Spei TransactionAuthorize :: placeRequest',
            [
                'request'  => $request,
                'response' => $response
            ]
        );

        $response['payment_method_details'] = $request['payment_method_details'];

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
        $this->conektaLogger->info('HTTP Client Spei TransactionAuthorize :: generateResponseForCode');

        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID'      => $txn_id,
                'ORD_ID'      => $ord_id
            ]
        );
    }
}
