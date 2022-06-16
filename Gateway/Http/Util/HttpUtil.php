<?php

namespace Conekta\Payments\Gateway\Http\Util;

use Conekta\Conekta;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Framework\Validator\Exception;

class HttpUtil
{
    /**
     * @param ConektaHelper $conektaHelper
     */
    public function __construct(
        protected ConektaHelper $conektaHelper
    ) {
    }

    /**
     * @param $config
     * @return void
     * @throws Exception
     */
    public function setupConektaClient($config): void
    {
        try {
            $locale = $config['locale'];
            $privateKey = $this->conektaHelper->getPrivateKey();
            $apiVersion = $this->conektaHelper->getApiVersion();
            $pluginType = $this->conektaHelper->pluginType();
            $pluginVersion = $this->conektaHelper->pluginVersion();

            if (empty($privateKey) && ! empty($locale)) {
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
