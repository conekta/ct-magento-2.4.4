<?php

namespace Conekta\Payments\Block\CreditCard;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info;
use Magento\Payment\Model\Config;

/**
 * Class CcInfo
 */
class CcInfo extends Info
{
    /**
     * @var string
     */
    protected $_template = 'Conekta_Payments::info/creditcard.phtml';
    protected Config $_paymentConfig;

    /**
     * CcInfo construct
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
     * Get Cc type Name
     *
     * @return Phrase|mixed
     * @throws LocalizedException
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
     * Get CC additional data
     *
     * @return false|mixed
     * @throws LocalizedException
     */
    public function getAdditionalData()
    {
        $information = $this->getInfo()->getAdditionalInformation();
        if (isset($information['additional_data'])) {
            return $information['additional_data'];
        }

        return false;
    }
}
