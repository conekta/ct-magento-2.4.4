<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class ShippingContactBuilder implements BuilderInterface
{
    public function __construct(
        private SubjectReader $subjectReader,
        private ConektaLogger $conektaLogger,
        private ConektaHelper $conektaHelper
    ) {
        $this->conektaLogger->info('Request ShippingContactBuilder :: __construct');
    }

    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request ShippingContactBuilder :: build');

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $quoteId = $payment->getAdditionalInformation('quote_id');

        $request['shipping_contact'] = $this->conektaHelper->getShippingContact($quoteId);

        if (empty($request['shipping_contact'])) {
            throw new LocalizedException(__('Missing shipping contacta information'));
        }

        $this->conektaLogger->info('Request ShippingContactBuilder :: build : return request', $request);

        return $request;
    }
}
