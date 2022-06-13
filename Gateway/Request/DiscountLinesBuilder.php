<?php

namespace Conekta\Payments\Gateway\Request;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class DiscountLinesBuilder implements BuilderInterface
{
    /**
     * @param ConektaLogger $conektaLogger
     * @param SubjectReader $subjectReader
     * @param CartRepositoryInterface $cartRepository
     * @param ConektaHelper $conektaHelper
     */
    public function __construct(
        private ConektaLogger             $conektaLogger,
        private SubjectReader             $subjectReader,
        protected CartRepositoryInterface $cartRepository,
        private ConektaHelper             $conektaHelper
    ) {
        $this->conektaLogger->info('Request DiscountLinesBuilder :: __construct');
    }

    /**
     * @param array $buildSubject
     * @return mixed
     */
    public function build(array $buildSubject)
    {
        $this->conektaLogger->info('Request DiscountLinesBuilder :: build');

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $quote_id = $payment->getAdditionalInformation('quote_id');
        $quote = $this->cartRepository->get($quote_id);
        $totalDiscount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        $totalDiscount = abs(round($totalDiscount, 2));

        if (! empty($totalDiscount)) {
            $totalDiscount = $this->conektaHelper->convertToApiPrice($totalDiscount);
            $discountLine['code'] = 'discount_code';
            $discountLine['type'] = 'coupon';
            $discountLine['amount'] = $totalDiscount;
            $request['discount_lines'][] = $discountLine;
        } else {
            $request['discount_lines'] = [];
        }

        $this->conektaLogger->info('Request DiscountLinesBuilder :: build : return request', $request);

        return $request;
    }

    /**
     * @param $lines
     * @param $line
     * @return array
     */
    private function _mergeLines($lines, $line): array
    {
        return array_merge($lines, [$line]);
    }
}
