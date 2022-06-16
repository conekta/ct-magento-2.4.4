<?php

namespace Conekta\Payments\Gateway\Response\CreditCard;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class FraudHandler implements HandlerInterface
{
    public const FRAUD_MSG_LIST = 'FRAUD_MSG_LIST';

    /**
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(private ConektaLogger $conektaLogger)
    {
        $this->conektaLogger->info('Response FraudHandler :: __construct');
    }

    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->conektaLogger->info('Response FraudHandler :: handle');

        if (! isset($response[self::FRAUD_MSG_LIST]) || ! is_array($response[self::FRAUD_MSG_LIST])) {
            return;
        }

        if (! isset($handlingSubject['payment'])
            || ! $handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        $payment->setAdditionalInformation(
            self::FRAUD_MSG_LIST,
            (array)$response[self::FRAUD_MSG_LIST]
        );

        /** @var $payment Payment */
        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
    }
}
