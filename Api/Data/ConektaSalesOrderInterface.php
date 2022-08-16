<?php

namespace Conekta\Payments\Api\Data;

/**
 * Interface ConektaSalesOrderInterface
 */
interface ConektaSalesOrderInterface
{
    public const CONEKTA_ORDER_ID = 'conekta_order_id';
    public const INCREMENT_ORDER_ID = 'increment_order_id';

    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get conekta Order ID
     *
     * @return mixed
     */
    public function getConektaOrderId();

    /**
     * Set Conekta order ID
     *
     * @param $value
     * @return mixed
     */
    public function setConektaOrderId($value);

    /**
     * Gets the Sales Increment Order ID
     *
     * @return string|null Sales Increment Order ID.
     */
    public function getIncrementOrderId(): ?string;

    /**
     * Set increment Order ID
     *
     * @param $value
     * @return mixed
     */
    public function setIncrementOrderId($value);
}
