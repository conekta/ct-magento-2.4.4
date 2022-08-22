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
    private ConektaHelper $conektaHelper;
    private ConektaLogger $conektaLogger;
    private ClassModel $taxClass;
    private Product $product;

    public function __construct(
        Product $product,
        ClassModel $taxClass,
        ConektaLogger $conektaLogger,
        ConektaHelper $conektaHelper
    ) {
        $this->product = $product;
        $this->taxClass = $taxClass;
        $this->conektaLogger = $conektaLogger;
        $this->conektaHelper = $conektaHelper;
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
