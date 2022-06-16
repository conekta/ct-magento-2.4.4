<?php

namespace Conekta\Payments\Gateway\Response\CreditCard;

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
        $this->conektaLogger->info('Response TxnIdHandler :: __construct');
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
        $this->conektaLogger->info('Response TxnIdHandler :: handle');

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();

        $order->setExtOrderId($response[self::ORD_ID]);

        if (isset($response['payment_method_details']['payment_method']['monthly_installments'])
            && ! empty($response['payment_method_details']['payment_method']['monthly_installments'])) {
            $installments = $response['payment_method_details']['payment_method']['monthly_installments'];
            $order->addStatusHistoryComment(__('Monthly installments select %1 months', $installments));
        }

        $data = [
                'cc_type'      => $payment->getAdditionalInformation('cc_type'),
                'cc_exp_year'  => $payment->getAdditionalInformation('cc_exp_year'),
                'cc_exp_month' => $payment->getAdditionalInformation('cc_exp_month'),
                'cc_bin'       => $payment->getAdditionalInformation('cc_bin'),
                'cc_last_4'    => $payment->getAdditionalInformation('cc_last_4'),
                'card_token'   => $payment->getAdditionalInformation('card_token')
        ];

        $payment->setCcType($payment->getAdditionalInformation('cc_type'));
        $payment->setCcExpMonth($payment->getAdditionalInformation('cc_exp_month'));
        $payment->setCcExpYear($payment->getAdditionalInformation('cc_exp_year'));
        $payment->setAdditionalInformation('additional_data', $data);
        $payment->unsAdditionalInformation('cc_type');
        $payment->unsAdditionalInformation('cc_exp_year');
        $payment->unsAdditionalInformation('cc_exp_month');
        $payment->unsAdditionalInformation('cc_bin');
        $payment->unsAdditionalInformation('cc_last_4');
        $payment->unsAdditionalInformation('card_token');
        $payment->setIsTransactionPending(false);
        $payment->setTransactionId($response[self::TXN_ID]);
        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);
    }
}
