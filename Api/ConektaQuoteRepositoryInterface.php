<?php

namespace Conekta\Payments\Api;

use Conekta\Payments\Api\Data\ConektaQuoteInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ConektaQuoteRepositoryInterface
 * @package Conekta\Payments\Api
 */
interface ConektaQuoteRepositoryInterface
{
    /**
     * @param int $id
     * @return ConektaQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getById($id): ConektaQuoteInterface;

    /**
     * @param ConektaQuoteInterface $conektaQuote
     * @return ConektaQuoteInterface
     */
    public function save(ConektaQuoteInterface $conektaQuote): ConektaQuoteInterface;
}
