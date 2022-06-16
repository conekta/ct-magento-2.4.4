<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\Escaper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class MetadataBuilder implements BuilderInterface
{
    /**
     * @param Escaper $_escaper
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        protected Escaper $_escaper,
        protected ConektaHelper $conektaHelper,
        private ConektaLogger $conektaLogger,
        private SubjectReader $subjectReader
    ) {
        $this->conektaLogger->info('Request MetadataBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return mixed
     */
    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request MetadataBuilder :: build');

        if (! isset($buildSubject['payment'])
            || ! $buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $this->subjectReader->readPayment($buildSubject);
        $order = $payment->getOrder();
        $items = $order->getItems();
        $request['metadata'] = $this->conektaHelper->getMetadataAttributesConekta($items);

        $this->conektaLogger->info('Request MetadataBuilder :: build : return request', $request);

        return $request;
    }
}
