<?php

namespace Conekta\Payments\Api;

use Conekta\Order;

/**
 * Interface EmbedFormRepositoryInterface
 * @package Conekta\Payments\Api
 */
interface EmbedFormRepositoryInterface
{
    /**
     * @param int $quoteId
     * @param array $orderParams
     * @param float $orderTotal
     * @return Order
     */
    public function generate(int $quoteId, array $orderParams, float $orderTotal): Order;
}
