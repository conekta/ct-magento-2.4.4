<?php

namespace Conekta\Payments\Model\Ui\CreditCard;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Magento\Checkout\Model\{ConfigProviderInterface, Session};
use Magento\Framework\Exception\{LocalizedException, NoSuchEntityException};
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Model\CcConfig;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'conekta_cc';

    /**
     * @param Repository $assetRepository
     * @param CcConfig $ccCongig
     * @param ConektaHelper $conektaHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        protected Repository $assetRepository,
        protected CcConfig $ccCongig,
        protected ConektaHelper $conektaHelper,
        protected Session $checkoutSession
    ) {
    }

    /**
     * @return array[][]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'availableTypes'                      => $this->getCcAvalaibleTypes(),
                    'months'                              => $this->_getMonths(),
                    'years'                               => $this->_getYears(),
                    'hasVerification'                     => true,
                    'cvvImageUrl'                         => $this->getCvvImageUrl(),
                    'monthly_installments'                => $this->getMonthlyInstallments(),
                    'active_monthly_installments'         => $this->getActiveMonthlyInstallments(),
                    'minimum_amount_monthly_installments' => $this->getMinimumAmountMonthlyInstallments(),
                    'total'                               => $this->getQuote()->getGrandTotal()
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCcAvalaibleTypes(): array
    {
        $result = [];
        $cardTypes = $this->ccCongig->getCcAvailableTypes();
        $cc_types = explode(',', $this->conektaHelper->getConfigData('conekta_cc', 'cctypes'));
        if (! empty($cc_types)) {
            foreach ($cc_types as $key) {
                if (isset($cardTypes[$key])) {
                    $result[$key] = $cardTypes[$key];
                }
            }
        }
        return $result;
    }

    /**
     * @return array
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
     * @return bool
     */
    public function getActiveMonthlyInstallments(): bool
    {
        $isActive = $this->conektaHelper->getConfigData('conekta/conekta_creditcard', 'active_monthly_installments');

        return ! ($isActive == '0');
    }

    /**
     * @return string
     */
    public function getCvvImageUrl(): string
    {
        return $this->assetRepository->getUrl('Conekta_Payments::images/cvv.png');
    }

    /**
     * @return string[]
     */
    private function _getMonths(): array
    {
        return [
            '1'  => '01 - Enero',
            '2'  => '02 - Febrero',
            '3'  => '03 - Marzo',
            '4'  => '04 - Abril',
            '5'  => '05 - Mayo',
            '6'  => '06 - Junio',
            '7'  => '07 - Julio',
            '8'  => '08 - Augosto',
            '9'  => '09 - Septiembre',
            '10' => '10 - Octubre',
            '11' => '11 - Noviembre',
            '12' => '12 - Diciembre'
        ];
    }

    /**
     * @return array
     */
    private function _getYears(): array
    {
        $years = [];
        $cYear = (int) date('Y');
        $cYear = --$cYear;
        for ($i = 1; $i <= 8; $i++) {
            $year = (string) ($cYear + $i);
            $years[$year] = $year;
        }

        return $years;
    }

    /**
     * @return array
     */
    private function _getStartYears(): array
    {
        $years = [];
        $cYear = (int) date('Y');

        for ($i = 5; $i >= 0; $i--) {
            $year = (string)($cYear - $i);
            $years[$year] = $year;
        }

        return $years;
    }

    /**
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote(): CartInterface|Quote
    {
        return $this->checkoutSession->getQuote();
    }
}
