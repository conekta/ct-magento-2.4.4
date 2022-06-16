<?php

namespace Conekta\Payments\Block\Info;

use Magento\Checkout\Block\Onepage\Success as CompleteCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\{ScopeInterface, Store};

class Success extends CompleteCheckout
{
    /**
     * getInstructions getter
     * @return Order Object
     */
    public function getInstructions($type): Order
    {
        if ($type == 'oxxo') {
            return $this->_scopeConfig->getValue(
                'payment/conekta_oxxo/instructions',
                ScopeInterface::SCOPE_STORE
            );
        }
        if ($type == 'spei') {
            return $this->_scopeConfig->getValue(
                'payment/conekta_spei/instructions',
                ScopeInterface::SCOPE_STORE
            );
        }
    }

    /**
     * getMethod getter
     * @return string Object
     */
    public function getMethod(): string
    {
        return $this->getOrder()->getPayment()->getMethod();
    }

    /**
     *  getOfflineInfo getter
     * @return Order Object
     * @throws LocalizedException
     */
    public function getOfflineInfo(): Order
    {
        return $this->getOrder()
            ->getPayment()
            ->getMethodInstance()
            ->getInfoInstance()
            ->getAdditionalInformation('offline_info');
    }
    /**
     * getOrder getter
     * @return Order Object
     */
    public function getOrder(): Order
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
    /**
     * getAccountOwner getter
     * @return Store Instance
     */
    public function getAccountOwner(): Store
    {
        return $this->_scopeConfig->getValue(
            'payment/conekta_spei/account_owner',
            ScopeInterface::SCOPE_STORE
        );
    }
}
