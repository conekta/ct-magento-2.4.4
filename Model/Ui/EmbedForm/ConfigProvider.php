<?php

namespace Conekta\Payments\Model\Ui\EmbedForm;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Checkout\Model\{ConfigProviderInterface, Session};
use Magento\Framework\Exception\{LocalizedException, NoSuchEntityException};
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Create Order Controller Path
     */
    public const CREATEORDER_URL = 'conekta/index/createorder';
    /**
     * Payment method code
     */
    public const CODE = 'conekta_ef';
    public const PAYMENT_METHOD_CREDIT_CARD = 'credit';
    public const PAYMENT_METHOD_OXXO = 'oxxo';
    public const PAYMENT_METHOD_SPEI = 'spei';

    /**
     * ConfigProvider constructor.
     * @param ConektaHelper $conektaHelper
     * @param Session $checkoutSession
     * @param ConektaLogger $conektaLogger
     * @param UrlInterface $url
     */
    public function __construct(
        protected ConektaHelper $conektaHelper,
        protected Session $checkoutSession,
        protected ConektaLogger $conektaLogger,
        protected UrlInterface $url
    ) {
    }

    /**
     * @return \array[][]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'hasVerification'                     => true,
                    'monthly_installments'                => $this->getMonthlyInstallments(),
                    'active_monthly_installments'         => $this->getMonthlyInstallments(),
                    'minimum_amount_monthly_installments' => $this->getMinimumAmountMonthlyInstallments(),
                    'total'                               => $this->getQuote()->getGrandTotal(),
                    'createOrderUrl'                      => $this->url->getUrl(self::CREATEORDER_URL),
                    'paymentMethods'                      => $this->getPaymentMethodsActive(),
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getEnableSaveCardConfig()
    {
        return $this->conektaHelper->getConfigData('conekta/conekta_global', 'enable_saved_card');
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getMonthlyInstallments(): array
    {
        $total = $this->getQuote()->getGrandTotal();
        $months = [1];
        if ((int)$this->getMinimumAmountMonthlyInstallments() < (int)$total) {
            $months = explode(
                ',',
                $this->conektaHelper->getConfigData('conekta_cc', 'monthly_installments')
            );

            if (! in_array('1', $months)) {
                array_push($months, '1');
            }

            asort($months);

            foreach ($months as $k => $v) {
                if ((int)$total < ($v * 100)) {
                    unset($months[$k]);
                }
            }
        }

        return $months;
    }

    /**
     * @return mixed
     */
    public function getMinimumAmountMonthlyInstallments()
    {
        return $this->conektaHelper->getConfigData('conekta_cc', 'minimum_amount_monthly_installments');
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

    /**
     * @return array
     */
    public function getPaymentMethodsActive(): array
    {
        $methods = [];

        if ($this->conektaHelper->isCreditCardEnabled()) {
            $methods[] = 'Card';
        }
        if ($this->conektaHelper->isOxxoEnabled()) {
            $methods[] = 'Cash';
        }
        if ($this->conektaHelper->isSpeiEnabled()) {
            $methods[] = 'BankTransfer';
        }
        return $methods;
    }
}
