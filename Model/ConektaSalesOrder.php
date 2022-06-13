<?php

namespace Conekta\Payments\Model;

use Conekta\Payments\Api\Data\ConektaSalesOrderInterface;
use Conekta\Payments\Model\ResourceModel\ConektaSalesOrder as ResourceConektaSalesOrder;
use Magento\Framework\Model\AbstractModel;

class ConektaSalesOrder extends AbstractModel implements ConektaSalesOrderInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceConektaSalesOrder::class);
    }

    /**
     * @param $value
     * @return void
     */
    public function setConektaOrderId($value): void
    {
        $this->setData(ConektaSalesOrderInterface::CONEKTA_ORDER_ID, $value);
    }

    /**
     * @return array|mixed|null
     */
    public function getConektaOrderId()
    {
        return $this->getData(ConektaSalesOrderInterface::CONEKTA_ORDER_ID);
    }

    /**
     * @param $value
     * @return void
     */
    public function setIncrementOrderId($value): void
    {
        $this->setData(ConektaSalesOrderInterface::INCREMENT_ORDER_ID, $value);
    }

    /**
     * @return string|null
     */
    public function getIncrementOrderId(): ?string
    {
        return $this->getData(ConektaSalesOrderInterface::INCREMENT_ORDER_ID);
    }

    /**
     * @param $conektaOrderId
     * @return $this
     */
    public function loadByConektaOrderId($conektaOrderId): self
    {
        return $this->loadByAttribute(ConektaSalesOrderInterface::CONEKTA_ORDER_ID, $conektaOrderId);
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
