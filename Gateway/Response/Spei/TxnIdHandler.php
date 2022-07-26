<?php

namespace Conekta\Payments\Gateway\Response\Spei;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class TxnIdHandler implements HandlerInterface
{
    public const TXN_ID = 'TXN_ID';
    public const ORD_ID = 'ORD_ID';

    /**
     * TxnIdHandler constructor.
     * @param ConektaLogger $conektaLogger
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        private ConektaLogger $conektaLogger,
        private SubjectReader $subjectReader
    ) {
        $this->conektaLogger->info('Response Spei TxnIdHandler :: __construct');
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $this->conektaLogger->info('Response Spei TxnIdHandler :: handle');

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $order->setExtOrderId($response[self::ORD_ID]);

        $payment->setTransactionId($response[self::TXN_ID]);
        $payment->setAdditionalInformation('offline_info', $response['offline_info']);
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
