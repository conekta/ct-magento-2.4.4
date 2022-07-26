<?php

namespace Conekta\Payments\Model\ResourceModel\ConektaSalesOrder;

use Conekta\Payments\Model\ConektaSalesOrder;
use Conekta\Payments\Model\ResourceModel\ConektaSalesOrder as ResourceConektaSalesOrder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            ConektaSalesOrder::class,
            ResourceConektaSalesOrder::class
        );
    }
}
