<?php

namespace Conekta\Payments\Observer;

use Conekta\Payments\Model\Config;
use Magento\Framework\Event\{Observer, ObserverInterface};
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Validator\Exception;

/**
 * Class CreateWebhook
 */
class Webhook implements ObserverInterface
{
    /**
     * @param Config $config
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected Config $config,
        protected ManagerInterface $messageManager
    ) {
    }

    /**
     * Create Webhook
     *
     * @param Observer $observer
     * @throws  Exception
     */
    public function execute(Observer $observer): void
    {
        $this->config->createWebhook();
    }
}
