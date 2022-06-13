<?php

namespace Conekta\Payments\Gateway\Config\EmbedForm;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

class ActiveValueHandler implements ValueHandlerInterface
{
    /**
     * @param ConektaHelper $_conektaHelper
     */
    public function __construct(
        protected ConektaHelper $_conektaHelper
    ) {
    }

    /**
     * @param array $subject
     * @param $storeId
     * @return bool
     */
    public function handle(array $subject, $storeId = null): bool
    {
        return $this->_conektaHelper->isCreditCardEnabled()
               || $this->_conektaHelper->isOxxoEnabled()
               || $this->_conektaHelper->isSpeiEnabled();
    }
}
