<?php

namespace Conekta\Payments\Block\Oxxo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info;
use Magento\Payment\Model\Config;

/**
 * Class OxxoInfo
 */
class OxxoInfo extends Info
{
    protected $_template = 'Conekta_Payments::info/oxxo.phtml';

    /**
     * Construct OxxoInfo
     *
     * @param Context $context
     * @param Config $_paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected Config $_paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get Oxxo Data
     *
     * @return false|mixed
     * @throws LocalizedException
     */
    public function getDataOxxo()
    {
        $additional_data = $this->getAdditionalData();
        if (isset($additional_data['offline_info']['data'])) {
            return $additional_data['offline_info']['data'];
        }

        return false;
    }

    /**
     * Get Additional Data
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getAdditionalData()
    {
        return $this->getInfo()->getAdditionalInformation();
    }
}
