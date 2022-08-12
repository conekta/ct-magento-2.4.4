<?php

namespace Conekta\Payments\Gateway\Config\CreditCard;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Class PaymentActionValueHandler
 */
class PaymentActionValueHandler implements ValueHandlerInterface
{
    /**
     * PaymentActionValueHandler construct
     *
     * @param ConektaHelper $_conektaHelper
     */
    public function __construct(
        protected ConektaHelper $_conektaHelper
    ) {
    }

    /**
     * PaymentActionValueHandler handler function
     *
     * @param array $subject
     * @param $storeId
     * @return string
     */
    public function handle(array $subject, $storeId = null): string
    {
        return 'authorize_capture';
    }
}
