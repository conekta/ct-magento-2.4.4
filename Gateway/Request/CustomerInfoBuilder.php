<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Catalog\Model\Product;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CustomerInfoBuilder implements BuilderInterface
{
    /**
     * @param Product $product
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        private Product $product,
        private ConektaLogger $conektaLogger
    ) {
        $this->conektaLogger->info('Request LineItemsBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return mixed
     */
    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request CustomerInfoBuilder :: build');

        if (! isset($buildSubject['payment'])
            || ! $buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        $billing = $order->getBillingAddress();

        $request['customer_info'] = [
            'name'     => $this->getCustomerName($order),
            'email'    => $billing->getEmail(),
            'phone'    => $billing->getTelephone(),
            'metadata' => [
                'soft_validations' => true
            ]
        ];

        $this->conektaLogger->info('Request CustomerInfoBuilder :: build : return request', $request);

        return $request;
    }

    /**
     * @param $order
     * @return string
     */
    public function getCustomerName($order): string
    {
        $billing = $order->getBillingAddress();

        return sprintf(
            '%s %s %s',
            $billing->getFirstName(),
            $billing->getMiddleName(),
            $billing->getLastName()
        );
    }
}
