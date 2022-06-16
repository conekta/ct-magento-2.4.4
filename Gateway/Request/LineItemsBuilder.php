<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class LineItemsBuilder implements BuilderInterface
{
    /**
     * @param Product $product
     * @param Escaper $escaper
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        private Product $product,
        private Escaper $escaper,
        protected ConektaHelper $conektaHelper,
        protected ConektaLogger $conektaLogger
    ) {
        $this->conektaLogger->info('Request LineItemsBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return mixed
     */
    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request LineItemsBuilder :: build');

        if (! isset($buildSubject['payment'])
            || ! $buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $items = $order->getItems();
        $request['line_items'] = $this->conektaHelper->getLineItems($items, false);

        $this->conektaLogger->info('Request LineItemsBuilder :: build : return request', $request);

        return $request;
    }
}
