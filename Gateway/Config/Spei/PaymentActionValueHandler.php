<?php

namespace Conekta\Payments\Gateway\Config\Spei;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

class PaymentActionValueHandler implements ValueHandlerInterface
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
     * @return string
     */
    public function handle(array $subject, $storeId = null): string
    {
        return 'authorize';
    }
}
