<?php

namespace Conekta\Payments\Api\Data;

/**
 * Interface ConektaQuoteInterface
 */
interface ConektaQuoteInterface
{
    public const QUOTE_ID = 'quote_id';
    public const CONEKTA_ORDER_ID = 'conekta_order_id';
    public const MINIMUM_AMOUNT_PER_QUOTE = 20;

    /**
     * Get quote ID
     *
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * Set Quote ID
     *
     * @param int $value
     * @return void
     */
    public function setQuoteId($value): void;

    /**
     * Get Conekta Order ID
     *
     * @return string
     */
    public function getConektaOrderId(): string;

    /**
     * Set conekta Order ID
     *
     * @param string $value
     * @return void
     */
    public function setConektaOrderId($value): void;
}
