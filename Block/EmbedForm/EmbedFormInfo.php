<?php

namespace Conekta\Payments\Block\EmbedForm;

use Conekta\Payments\Model\Ui\EmbedForm\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\{Exception, Phrase};
use Magento\Payment\Block\Info;
use Magento\Payment\Model\Config;

class EmbedFormInfo extends Info
{
    /**
     * @var string
     */
    protected $_template = 'Conekta_Payments::info/embedform.phtml';

    /**
     * @param Context $context
     * @param Config $_paymentConfig
     * @param array $data
     */
    public function __construct(
        Context          $context,
        protected Config $_paymentConfig,
        array            $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return Phrase|mixed
     * @throws Exception\LocalizedException
     */
    public function getCcTypeName()
    {
        $types = $this->_paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }
        return empty($ccType) ? __('N/A') : $ccType;
    }

    /**
     * @return mixed
     * @throws Exception\LocalizedException
     */
    public function getAdditionalData()
    {
        return $this->getInfo()->getAdditionalInformation();
    }

    /**
     * @return false|mixed
     * @throws Exception\LocalizedException
     */
    public function getOfflineInfo()
    {
        $additional_data = $this->getAdditionalData();
        if (isset($additional_data['offline_info']['data'])) {
            return $additional_data['offline_info']['data'];
        }

        return false;
    }

    /**
     * @return mixed
     * @throws Exception\LocalizedException
     */
    public function getPaymentMethodType()
    {
        return $this->getInfo()->getAdditionalInformation('payment_method');
    }

    /**
     * @return string
     * @throws Exception\LocalizedException
     */
    public function getPaymentMethodTitle(): string
    {
        $methodType = $this->getPaymentMethodType();
        $title = '';

        switch ($methodType) {
            case ConfigProvider::PAYMENT_METHOD_CREDIT_CARD:
                $title = 'Tarjeta de Crédito';
                break;
            case ConfigProvider::PAYMENT_METHOD_OXXO:
                $title = 'Pago en Efectivo con Oxxo';
                break;
            case ConfigProvider::PAYMENT_METHOD_SPEI:
                $title = 'Transferencia SPEI';
                break;
        }

        return $title;
    }

    /**
     * @return bool
     * @throws Exception\LocalizedException
     */
    public function isCreditCardPaymentMethod(): bool
    {
        return $this->getPaymentMethodType() === ConfigProvider::PAYMENT_METHOD_CREDIT_CARD;
    }

    /**
     * @return bool
     * @throws Exception\LocalizedException
     */
    public function isOxxoPaymentMethod(): bool
    {
        return $this->getPaymentMethodType() === ConfigProvider::PAYMENT_METHOD_OXXO;
    }

    /**
     * @return bool
     * @throws Exception\LocalizedException
     */
    public function isSpeiPaymentMethod(): bool
    {
        return $this->getPaymentMethodType() === ConfigProvider::PAYMENT_METHOD_SPEI;
    }
}
