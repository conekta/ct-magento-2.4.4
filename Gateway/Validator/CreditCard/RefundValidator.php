<?php

namespace Conekta\Payments\Gateway\Validator\CreditCard;

use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Exception;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\{AbstractValidator, ResultInterface, ResultInterfaceFactory};

class RefundValidator extends AbstractValidator
{
    /**
     * RefundValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        private SubjectReader $subjectReader,
        protected ConektaHelper $conektaHelper,
        private ConektaLogger $conektaLogger
    ) {
        $this->conektaLogger->info('Credit Card RefundValidator :: __construct');
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        try {
            $response = $this->subjectReader->readResponse($validationSubject);
            $this->conektaLogger->info('RefundValidator :: handle');
            $this->conektaLogger->info('RefundValidator: response', [$response['refund_result']]);
            $errorMessages = [];
            $isValid = true;

            $transactionResult = $response['refund_result'];
            if ($transactionResult['status'] != 'SUCCESS') {
                $isValid = false;
                $errorMessages[] = $response['refund_result']['status_message'];
            }
        } catch (Exception $e) {
            $isValid = false;
            $errorMessages[] = $e->getMessage();
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
