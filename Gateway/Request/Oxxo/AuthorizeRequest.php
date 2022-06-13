<?php

namespace Conekta\Payments\Gateway\Request\Oxxo;

use Conekta\Payments\Gateway\Request\Contracts\AutorizeRequestInterface;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class AuthorizeRequest implements AutorizeRequestInterface
{
    /**
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        private ConfigInterface $config,
        private SubjectReader   $subjectReader,
        protected ConektaHelper $conektaHelper,
        private ConektaLogger   $conektaLogger
    ) {
        $this->conektaLogger->info('Request Oxxo AuthorizeRequest :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $this->conektaLogger->info('Request Oxxo AuthorizeRequest :: build');

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        $expiry_date = $this->conektaHelper->getExpiredAt();
        $amount = $this->conektaHelper->convertToApiPrice($order->getGrandTotalAmount());

        $request['metadata'] = [
            'plugin'                 => self::PluginName,
            'plugin_version'         => $this->conektaHelper->getMageVersion(),
            'plugin_conekta_version' => $this->conektaHelper->pluginVersion(),
            'order_id'               => $order->getOrderIncrementId(),
            'soft_validations'       => 'true'
        ];

        $request['payment_method_details'] = $this->getChargeOxxo($amount, $expiry_date);
        $request['CURRENCY'] = $order->getCurrencyCode();
        $request['TXN_TYPE'] = 'A';

        return $request;
    }

    /**
     * @param $amount
     * @param $expiry_date
     * @return array
     */
    public function getChargeOxxo($amount, $expiry_date): array
    {
        $charge = [
            'payment_method' => [
                'type'       => self::OxxoType,
                'expires_at' => $expiry_date
            ],
            'amount' => $amount
        ];
        return $charge;
    }
}
