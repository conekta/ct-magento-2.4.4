<?php

namespace Conekta\Payments\Api;

use Conekta\Payments\Api\Data\ConektaQuoteInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ConektaQuoteRepositoryInterface
 */
interface ConektaQuoteRepositoryInterface
{
    /**
     * Get Quote by ID
     *
     * @param int $id
     * @return ConektaQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getById($id): ConektaQuoteInterface;

    /**
     * Save conekta quote
     *
     * @param ConektaQuoteInterface $conektaQuote
     * @return ConektaQuoteInterface
     */
    public function save(ConektaQuoteInterface $conektaQuote): ConektaQuoteInterface;
}
