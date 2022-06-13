<?php

namespace Conekta\Payments\Model;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\{Conekta, Webhook};
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Validator\Exception;

class Config
{
    /**
     * @param EncryptorInterface $encryptor
     * @param ConektaHelper $conektaHelper
     * @param Resolver $resolver
     * @param ConektaLogger $conektaLogger
     * @param Webhook $conektaWebhook
     */
    public function __construct(
        protected EncryptorInterface $encryptor,
        protected ConektaHelper $conektaHelper,
        protected Resolver $resolver,
        private ConektaLogger $conektaLogger,
        protected Webhook $conektaWebhook
    ) {
    }

    /**
     * @return void
     * @throws Exception
     */
    public function createWebhook(): void
    {
        try {
            $sandboxMode = $this->conektaHelper->getConfigData('conekta/conekta_global', 'sandbox_mode');
            $urlWebhook = $this->conektaHelper->getUrlWebhookOrDefault();

            $events = ['events' => ['charge.paid']];
            $errorMessage = null;

            //If library can't be initialized throws exception
            $this->initializeConektaLibrary();

            $different = true;
            $webhooks = $this->conektaWebhook->where();
            foreach ($webhooks as $webhook) {
                if (strpos($webhook->webhook_url, $urlWebhook) !== false) {
                    $different = false;
                }
            }
            if ($different) {
                if (! $sandboxMode) {
                    $mode = [
                        'production_enabled' => 1
                    ];
                } else {
                    $mode = [
                        'development_enabled' => 1
                    ];
                }
                $this->conektaWebhook->create(
                    array_merge(['url' => $urlWebhook], $mode, $events)
                );
            } else {
                $this->conektaLogger->info(
                    '[Conekta]: El webhook ' . $urlWebhook . ' ya se encuentra en Conekta!'
                );
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->conektaLogger->info(
                '[Conekta]: CreateWebhook error, Message: ' . $errorMessage
            );
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->conektaLogger->info(
                '[Conekta]: Webhook error, Message: ' . $errorMessage . ' URL: ' . $urlWebhook
            );

            throw new Exception(
                __(
                    'Can not register this webhook ' . $urlWebhook . '<br>'
                    . 'Message: ' . (string)$errorMessage
                )
            );
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function initializeConektaLibrary(): void
    {
        try {
            $lang = explode('_', $this->resolver->getLocale());
            $locale = $lang[0] == 'es' ? 'es' : 'en';
            $privateKey = $this->conektaHelper->getPrivateKey();
            $apiVersion = $this->conektaHelper->getApiVersion();
            $pluginType = $this->conektaHelper->pluginType();
            $pluginVersion = $this->conektaHelper->pluginVersion();

            if (empty($privateKey)) {
                throw new Exception(
                    __('Please check your conekta config.')
                );
            }

            Conekta::setApiKey($privateKey);
            Conekta::setApiVersion($apiVersion);
            Conekta::setPlugin($pluginType);
            Conekta::setPluginVersion($pluginVersion);
            Conekta::setLocale($locale);
        } catch (\Exception $e) {
            throw new Exception(
                __($e->getMessage())
            );
        }
    }
}
