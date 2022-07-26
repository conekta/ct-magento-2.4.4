<?php

namespace Conekta\Payments\Api\Data;

/**
 * Interface ConektaSalesOrderInterface
 * @package Conekta\Payments\Api\Data
 */
interface ConektaSalesOrderInterface
{
    public const CONEKTA_ORDER_ID = 'conekta_order_id';
    public const INCREMENT_ORDER_ID = 'increment_order_id';

    public function getId();

    public function getConektaOrderId();

    public function setConektaOrderId($value);

    /**
     * Gets the Sales Increment Order ID
     *
     * @return string|null Sales Increment Order ID.
     */
    public function getIncrementOrderId(): ?string;

    public function setIncrementOrderId($value);
}
