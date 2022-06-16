<?php

namespace Conekta\Payments\Gateway\Http\Client\CreditCard;

use Conekta\Order as ConektaOrder;
use Conekta\Payments\Gateway\Http\Util\HttpUtil;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Validator\Exception;
use Magento\Payment\Gateway\Http\{ClientInterface, TransferInterface};
use Magento\Payment\Model\Method\Logger;

class TransactionRefund implements ClientInterface
{
    /**
     * @param ConektaHelper $_conektaHelper
     * @param ConektaLogger $conektaLogger
     * @param ConektaOrder $conektaOrder
     * @param HttpUtil $_httpUtil
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param Logger $logger
     * @param array $data
     * @throws Exception
     */
    public function __construct(
        protected ConektaHelper $_conektaHelper,
        private ConektaLogger $conektaLogger,
        private ConektaOrder $conektaOrder,
        protected HttpUtil $_httpUtil,
        private Context $context,
        private EncryptorInterface $encryptor,
        private Logger $logger,
        array $data = []
    ) {
        $config = [
            'locale' => 'es'
        ];

        $this->conektaLogger->info('HTTP Client CreditCard TransactionRefund :: __construct');
        $this->_httpUtil->setupConektaClient($config);
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $this->conektaLogger->info('HTTP Client CreditCard TransactionRefund :: placeRequest');

        $request = $transferObject->getBody();
        $transactionId = $request['payment_transaction_id'];
        $amount = (int)($request['payment_transaction_amount'] * 100);
        $response = [];
        $response['refund_result']['transaction_id'] = $transactionId;
        try {
            $order = $this->conektaOrder->find($transactionId);
            $order->refund([
                'reason' => 'requested_by_client',
                'amount' => $amount
            ]);
            $response['refund_result']['status'] = 'SUCCESS';
            $response['refund_result']['status_message'] = 'Refunded';
        } catch (\Exception $e) {
            $error_code = $e->getMessage();
            $this->logger->debug(
                [
                    'transaction_id' => $transactionId,
                    'exception'      => $e->getMessage()
                ]
            );

            $this->conektaLogger->info(
                'HTTP Client  CreditCard TransactionRefund :: placeRequest: Payment refund error ' . $error_code
            );
            $response['refund_result']['status'] = 'ERROR';
            $response['refund_result']['status_message'] = $error_code;
        }

        $response['transaction_id'] = $transactionId;

        $this->conektaLogger->info(
            'HTTP Client TransactionCapture :: placeRequest',
            [
                'request'  => $request,
                'response' => $response
            ]
        );

        return $response;
    }
}
