<?php

namespace Conekta\Payments\Gateway\Http;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Payment\Gateway\Http\{TransferBuilder, TransferFactoryInterface, TransferInterface};

class TransferFactory implements TransferFactoryInterface
{
    private ConektaLogger $conektaLogger;
    private TransferBuilder $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        ConektaLogger $conektaLogger
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->conektaLogger = $conektaLogger;
        $this->conektaLogger->info('HTTP TransferFactory :: __construct');
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request): TransferInterface
    {
        $this->conektaLogger->info('HTTP TransferFactory :: create');

        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
