<?php

namespace Conekta\Payments\Gateway\Config\EmbedForm;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

class PaymentActionValueHandler implements ValueHandlerInterface
{
    protected ConektaHelper $_conektaHelper;

    /**
     * @param ConektaHelper $_conektaHelper
     */
    public function __construct(
        ConektaHelper $_conektaHelper
    ) {
        $this->_conektaHelper = $_conektaHelper;
    }

    /**
     * @param array $subject
     * @param $storeId
     * @return string
     */
    public function handle(array $subject, $storeId = null): string
    {
        return 'authorize';
    }
}
