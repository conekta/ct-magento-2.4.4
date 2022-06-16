<?php

namespace Conekta\Payments\Model;

use Magento\Framework\Session\{SaveHandlerInterface, SessionManager, SessionStartChecker, SidResolverInterface, StorageInterface, ValidatorInterface};

class Session extends SessionManager
{
    protected $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(
        StorageInterface $storage
    ) {
        $this->storage = $storage;
    }

    /**
     * Set Promotion Code
     *
     * @param string|null
     * @return $this
     */
    public function setConektaCheckoutId($url): self
    {
        $this->storage->setData('conekta_checkout_id', $url);

        return $this;
    }

    /**
     * Retrieve promotion code from current session
     *
     * @return string|null
     */
    public function getConektaCheckoutId(): ?string
    {
        if ($this->storage->getData('conekta_checkout_id')) {
            return $this->storage->getData('conekta_checkout_id');
        }

        return null;
    }
}
