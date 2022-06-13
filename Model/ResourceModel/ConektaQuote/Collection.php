<?php

namespace Conekta\Payments\Model\ResourceModel\ConektaQuote;

use Conekta\Payments\Model\ConektaQuote;
use Conekta\Payments\Model\ResourceModel\ConektaQuote as ResourceConektaQuote;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            ConektaQuote::class,
            ResourceConektaQuote::class
        );
    }
}
