<?php

namespace Conekta\Payments\Gateway\Request\Contracts;

use Magento\Payment\Gateway\Request\BuilderInterface;

interface AutorizeRequestInterface extends BuilderInterface
{
    public const PluginName = 'Magento';
    public const OxxoType = 'oxxo_cash';
    public const SpeiType = 'spei';
}