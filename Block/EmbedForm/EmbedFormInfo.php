<?php

namespace Conekta\Payments\Block\EmbedForm;

use Conekta\Payments\Model\Ui\EmbedForm\ConfigProvider;
use Magento\Framework\Exception;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info;
use Magento\Payment\Model\Config;

/**
 * Class EmbedFormInfo
 */
class EmbedFormInfo extends Info
{
    /**
     * @var string
     */
    protected $_template = 'Conekta_Payments::info/embedform.phtml';
    protected Config $_paymentConfig;

    /**
     * EmbedFormInfo construct
     *
     * @param Context $context
     * @param Config $_paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $_paymentConfig,
        array $data = []
    ) {
        $this->_paymentConfig = $_paymentConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get cc type name
     *
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
     * Get additional Data
     *
     * @return mixed
     * @throws Exception\LocalizedException
     */
    public function getAdditionalData()
    {
        return $this->getInfo()->getAdditionalInformation();
    }

    /**
     * Get Offline info
     *
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
     * Get payment method type
     *
     * @return mixed
     * @throws Exception\LocalizedException
     */
    public function getPaymentMethodType()
    {
        return $this->getInfo()->getAdditionalInformation('payment_method');
    }

    /**
     * Get payment method title
     *
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
     * Is credit card payment method
     *
     * @return bool
     * @throws Exception\LocalizedException
     */
    public function isCreditCardPaymentMethod(): bool
    {
        return $this->getPaymentMethodType() === ConfigProvider::PAYMENT_METHOD_CREDIT_CARD;
    }

    /**
     * Is oxxo paymen method
     *
     * @return bool
     * @throws Exception\LocalizedException
     */
    public function isOxxoPaymentMethod(): bool
    {
        return $this->getPaymentMethodType() === ConfigProvider::PAYMENT_METHOD_OXXO;
    }

    /**
     * Is spei paymen method
     *
     * @return bool
     * @throws Exception\LocalizedException
     */
    public function isSpeiPaymentMethod(): bool
    {
        return $this->getPaymentMethodType() === ConfigProvider::PAYMENT_METHOD_SPEI;
    }
}
