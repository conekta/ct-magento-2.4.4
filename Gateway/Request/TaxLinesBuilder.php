<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Catalog\Model\Product;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Tax\Model\ClassModel;

class TaxLinesBuilder implements BuilderInterface
{
    public function __construct(
        private Product $product,
        private ClassModel $taxClass,
        private ConektaLogger $conektaLogger,
        private ConektaHelper $conektaHelper
    ) {
        $this->conektaLogger->info('Request TaxLinesBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $this->conektaLogger->info('Request TaxLinesBuilder :: build');

        if (! isset($buildSubject['payment']) || ! $buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $request = [];
        $request['tax_lines'] = $this->conektaHelper->getTaxLines($order->getItems());

        $this->conektaLogger->info('Request TaxLinesBuilder :: build : return request', $request);

        return $request;
    }
}
