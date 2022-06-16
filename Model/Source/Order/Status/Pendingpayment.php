<?php

namespace Conekta\Payments\Model\Source\Order\Status;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class Pendingpayment extends Status
{
    protected $_stateStatuses = [Order::STATE_PENDING_PAYMENT];
}
