<?php

namespace Conekta\Payments\Model\Ui\Spei;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Checkout\Model\{ConfigProviderInterface, Session};
use Magento\Framework\Exception\{LocalizedException, NoSuchEntityException};
use Magento\Framework\View\Asset\Repository;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'conekta_spei';
    protected ConektaHelper $conektaHelper;
    protected Repository $assetRepository;
    protected Session $checkoutSession;

    /**
     * @param Session $checkoutSession
     * @param Repository $assetRepository
     * @param ConektaHelper $conektaHelper
     */
    public function __construct(
        Session $checkoutSession,
        Repository $assetRepository,
        ConektaHelper $conektaHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->assetRepository = $assetRepository;
        $this->conektaHelper = $conektaHelper;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'total' => $this->getQuote()->getGrandTotal()
                ]
            ]
        ];
    }

    /**
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
