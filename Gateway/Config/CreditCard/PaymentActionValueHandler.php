<?php

namespace Conekta\Payments\Gateway\Config\CreditCard;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Class PaymentActionValueHandler
 */
class PaymentActionValueHandler implements ValueHandlerInterface
{
    protected ConektaHelper $_conektaHelper;

    /**
     * PaymentActionValueHandler construct
     *
     * @param ConektaHelper $_conektaHelper
     */
    public function __construct(
        ConektaHelper $_conektaHelper
    ) {
        $this->_conektaHelper = $_conektaHelper;
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
