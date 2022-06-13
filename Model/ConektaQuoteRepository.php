<?php

namespace Conekta\Payments\Model;

use Conekta\Payments\Api\ConektaQuoteRepositoryInterface;
use Conekta\Payments\Api\Data\ConektaQuoteInterface;
use Conekta\Payments\Model\ResourceModel\ConektaQuote as ConektaQuoteResource;
use Magento\Framework\Exception\{AlreadyExistsException, NoSuchEntityException};

class ConektaQuoteRepository implements ConektaQuoteRepositoryInterface
{
    /**
     * @param ConektaQuoteFactory $conektaQuoteFactory
     * @param ConektaQuoteResource $conektaQuoteResource
     */
    public function __construct(
        private ConektaQuoteFactory $conektaQuoteFactory,
        private ConektaQuoteResource $conektaQuoteResource
    ) {
    }

    /**
     * @param $id
     * @return ConektaQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getById($id): ConektaQuoteInterface
    {
        $conektaQuote = $this->conektaQuoteFactory->create();
        $this->conektaQuoteResource->load($conektaQuote, $id);
        if (! $conektaQuote->getId()) {
            throw new NoSuchEntityException(__('Unable to find conekta quote with ID "%1"', $id));
        }
        return $conektaQuote;
    }

    /**
     * @param ConektaQuoteInterface $conektaQuote
     * @return ConektaQuoteInterface
     * @throws AlreadyExistsException
     */
    public function save(ConektaQuoteInterface $conektaQuote): ConektaQuoteInterface
    {
        $this->conektaQuoteResource->save($conektaQuote);
        return $conektaQuote;
    }
}
