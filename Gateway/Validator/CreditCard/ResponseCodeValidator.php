<?php

namespace Conekta\Payments\Gateway\Validator\CreditCard;

use Conekta\Payments\Gateway\Http\Client\CreditCard\TransactionCapture;
use Magento\Payment\Gateway\Validator\{AbstractValidator, ResultInterface};

//use Conekta\Payments\Gateway\Http\Client\TransactionCapture;

class ResponseCodeValidator extends AbstractValidator
{
    public const RESULT_CODE = 'RESULT_CODE';

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (! isset($validationSubject['response']) || ! is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response): bool
    {
        return isset($response[self::RESULT_CODE]) && $response[self::RESULT_CODE] !== TransactionCapture::FAILURE;
    }
}
