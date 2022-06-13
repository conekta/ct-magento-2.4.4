<?php

namespace Conekta\Payments\Gateway\Request\EmbedForm;

use Conekta\Customer as ConektaCustomer;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\Payments\Model\Config;
use Conekta\Payments\Model\Ui\EmbedForm\ConfigProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CaptureRequest implements BuilderInterface
{
    /**
     * CaptureRequest constructor.
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     * @param Config $conektaConfig
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConektaCustomer $conektaCustomer
     */
    public function __construct(
        protected ConfigInterface $config,
        protected SubjectReader $subjectReader,
        protected ConektaHelper $conektaHelper,
        protected ConektaLogger $conektaLogger,
        protected Config $conektaConfig,
        protected CustomerSession $customerSession,
        protected CustomerRepositoryInterface $customerRepository,
        protected ConektaCustomer $conektaCustomer
    ) {
        $this->conektaLogger->info('EMBED Request CaptureRequest :: __construct');
    }

    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request CaptureRequest :: build');

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $this->conektaLogger->info('Request CaptureRequest :: build additional', $payment->getAdditionalInformation());
        $token = $payment->getAdditionalInformation('card_token');
        $savedCard = $payment->getAdditionalInformation('saved_card');
        $enableSavedCard = $payment->getAdditionalInformation('saved_card_later');
        $iframePayment = $payment->getAdditionalInformation('iframe_payment');
        $iframeOrderId = $payment->getAdditionalInformation('order_id');
        $txnId = $payment->getAdditionalInformation('txn_id');
        $conektaCustomerId = '';
        $amount = (int)($order->getGrandTotalAmount() * 100);

        $request['metadata'] = [
            'plugin'           => 'Magento',
            'plugin_version'   => $this->conektaHelper->getMageVersion(),
            'order_id'         => $order->getOrderIncrementId(),
            'soft_validations' => 'true'
        ];
        $request['payment_method_details'] = $this->getCharge($payment, $amount);
        $request['CURRENCY'] = $order->getCurrencyCode();
        $request['TXN_TYPE'] = 'A';
        $request['INVOICE'] = $order->getOrderIncrementId();
        $request['AMOUNT'] = number_format($order->getGrandTotalAmount(), 2);
        $request['iframe_payment'] = $iframePayment;
        $request['order_id'] = $iframeOrderId;
        $request['txn_id'] = $txnId;

        $request['CONNEKTA_CUSTOMER_ID'] = $conektaCustomerId ? [
                'customer_id' => $conektaCustomerId
        ] : '';

        $this->conektaLogger->info('Request CaptureRequest :: build : return request', $request);

        return $request;
    }

    private function getCharge($payment, $orderAmount)
    {
        $paymentMethod = $payment->getAdditionalInformation('payment_method');

        $charge = [
            'payment_method' => [
                'type' => $paymentMethod
            ],
            'amount' => $orderAmount
        ];
        switch ($paymentMethod) {
            case ConfigProvider::PAYMENT_METHOD_CREDIT_CARD:
                $token = $payment->getAdditionalInformation('card_token');
                $charge['payment_method']['token_id'] = $token;
                break;
            case ConfigProvider::PAYMENT_METHOD_OXXO:
            case ConfigProvider::PAYMENT_METHOD_SPEI:
                $reference = $payment->getAdditionalInformation('reference');
                $expireAt = $this->conektaHelper->getExpiredAt();
                $charge['payment_method']['reference'] = $reference;
                $charge['payment_method']['expires_at'] = $expireAt;
                break;
        }

        return $charge;
    }
}
