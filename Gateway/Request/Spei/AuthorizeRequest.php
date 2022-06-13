<?php

namespace Conekta\Payments\Gateway\Request\Spei;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizeRequest implements BuilderInterface
{
    /**
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        private ConfigInterface $config,
        private SubjectReader $subjectReader,
        protected ConektaHelper $conektaHelper,
        private ConektaLogger $conektaLogger
    ) {
        $this->conektaLogger->info('Request Spei AuthorizeRequest :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $this->conektaLogger->info('Request Spei AuthorizeRequest :: build');

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $expiry_date = strtotime('+' . $this->conektaHelper->getConfigData('conekta_spei', 'expiry_days') . ' days');
        $amount = $this->conektaHelper->convertToApiPrice($order->getGrandTotalAmount());

        $request['metadata'] = [
            'plugin'                 => 'Magento',
            'plugin_version'         => $this->conektaHelper->getMageVersion(),
            'plugin_conekta_version' => $this->_conektaHelper->pluginVersion(),
            'order_id'               => $order->getOrderIncrementId(),
            'soft_validations'       => 'true'
        ];

        $request['payment_method_details'] = $this->getChargeSpei($amount, $expiry_date);
        $request['CURRENCY'] = $order->getCurrencyCode();
        $request['TXN_TYPE'] = 'A';

        return $request;
    }

    /**
     * @param $amount
     * @param $expiry_date
     * @return array
     */
    public function getChargeSpei($amount, $expiry_date): array
    {
        $charge = [
            'payment_method' => [
                'type'       => 'spei',
                'expires_at' => $expiry_date
            ],
            'amount' => $amount
        ];
        return $charge;
    }
}
