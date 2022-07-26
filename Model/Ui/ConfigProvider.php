<?php

namespace Conekta\Payments\Model\Ui;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'conekta_global';

    /**
     * @param ConektaHelper $conektaHelper
     * @param AssetRepository $assetRepository
     */
    public function __construct(
        protected ConektaHelper $conektaHelper,
        private AssetRepository $assetRepository
    ) {
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'publicKey'    => $this->conektaHelper->getPublicKey(),
                    'conekta_logo' => $this->assetRepository->getUrl('Conekta_Payments::images/conekta.svg')
                ]
            ]
        ];
    }
}
