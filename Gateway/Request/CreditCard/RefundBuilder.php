<?php

namespace Conekta\Payments\Gateway\Request\CreditCard;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundBuilder implements BuilderInterface
{
    /**
     * @param SubjectReader $subjectReader
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        private SubjectReader $subjectReader,
        protected ConektaHelper $conektaHelper,
        private ConektaLogger $conektaLogger
    ) {
        $this->conektaLogger->info('Request RefundBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $this->conektaLogger->info('Request RefundBuilder :: build');
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $amount = $this->subjectReader->readAmount($buildSubject);

        $request['metadata'] = [
            'plugin'         => 'Magento',
            'plugin_version' => $this->conektaHelper->getMageVersion()
        ];

        $request = [
            'payment_transaction_id'     => $order->getExtOrderId(),
            'payment_transaction_amount' => $amount
        ];

        $this->conektaLogger->info('Request RefundBuilder :: build request', $request);

        return $request;
    }
}
