<?php

namespace Conekta\Payments\Gateway\Command;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Command\{CommandException, ResultInterface};
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\{ClientException, ClientInterface, ConverterException, TransferFactoryInterface};
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

class GatewayCommand implements CommandInterface
{
    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param ConektaLogger $conektaLogger
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        private BuilderInterface         $requestBuilder,
        private TransferFactoryInterface $transferFactory,
        private ClientInterface          $client,
        private ConektaLogger            $conektaLogger,
        private ?HandlerInterface        $handler = null,
        private ?ValidatorInterface      $validator = null
    ) {
        $this->conektaLogger->info('Command GatewayCommand :: __construct');
    }

    /**
     * @param array $commandSubject
     * @return ResultInterface|void|null
     * @throws CommandException
     * @throws ClientException
     * @throws ConverterException
     */
    public function execute(array $commandSubject)
    {
        $this->conektaLogger->info('Command GatewayCommand :: execute');

        // @TODO implement exceptions catching
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );
        $response = $this->client->placeRequest($transferO);
        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (! $result->isValid()) {
                $this->logExceptions($result->getFailsDescription());

                $errorMessages = [];
                foreach ($result->getFailsDescription() as $failPhrase) {
                    $errorMessages[] = (string)$failPhrase;
                }

                throw new CommandException(
                    __(implode('; ', $errorMessages))
                );
            }
        }

        $this->handler?->handle(
            $commandSubject,
            $response
        );
    }

    /**
     * @param Phrase[] $fails
     * @return void
     */
    private function logExceptions(array $fails): void
    {
        foreach ($fails as $failPhrase) {
            $this->conektaLogger->critical((string)$failPhrase);
        }
    }
}
