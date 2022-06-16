<?php

namespace Conekta\Payments\Model;

use Conekta\Payments\Api\Data\ConektaQuoteInterface;
use Conekta\Payments\Model\ResourceModel\ConektaQuote as ResourceConektaQuote;
use Magento\Framework\Model\AbstractModel;

class ConektaQuote extends AbstractModel implements ConektaQuoteInterface
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceConektaQuote::class);
    }

    /**
     * @param $value
     * @return void
     */
    public function setQuoteId($value): void
    {
        $this->setData(ConektaQuoteInterface::QUOTE_ID, $value);
    }

    /**
     * @return int
     */
    public function getQuoteId(): int
    {
        return $this->getData(ConektaQuoteInterface::QUOTE_ID);
    }

    /**
     * @param $value
     * @return void
     */
    public function setConektaOrderId($value): void
    {
        $this->setData(ConektaQuoteInterface::CONEKTA_ORDER_ID, $value);
    }

    /**
     * @return string
     */
    public function getConektaOrderId(): string
    {
        return $this->getData(ConektaQuoteInterface::CONEKTA_ORDER_ID);
    }

    /**
     * @param $conektaOrderId
     * @return $this
     */
    public function loadByConektaOrderId($conektaOrderId): ConektaQuote
    {
        return $this->loadByAttribute(ConektaQuoteInterface::CONEKTA_ORDER_ID, $conektaOrderId);
    }

    /**
     * Load order by custom attribute value. Attribute value should be unique
     *
     * @param string $attribute
     * @param string $value
     * @return $this
     */
    public function loadByAttribute($attribute, $value): self
    {
        $this->load($value, $attribute);

        return $this;
    }
}
