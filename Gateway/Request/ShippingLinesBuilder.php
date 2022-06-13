<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class ShippingLinesBuilder implements BuilderInterface
{
    /**
     * @param SubjectReader $subjectReader
     * @param ConektaLogger $conektaLogger
     * @param ConektaHelper $conektaHelper
     */
    public function __construct(
        private SubjectReader $subjectReader,
        private ConektaLogger $conektaLogger,
        private ConektaHelper $conektaHelper
    ) {
        $this->conektaLogger->info('Request ShippingLinesBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return mixed
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request ShippingLinesBuilder :: build');

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $quote_id = $payment->getAdditionalInformation('quote_id');

        $shippingLines = $this->conektaHelper->getShippingLines($quote_id);

        if (empty($shippingLines)) {
            throw new LocalizedException(__('Shippment information should be provided'));
        }

        $request['shipping_lines'] = $shippingLines;

        $this->conektaLogger->info('Request ShippingLinesBuilder :: build : return request', $request);

        return $request;
    }
}
