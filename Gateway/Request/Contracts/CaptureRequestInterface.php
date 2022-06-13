<?php

namespace Conekta\Payments\Gateway\Request\Contracts;

use Magento\Payment\Gateway\Request\BuilderInterface;

interface CaptureRequestInterface extends BuilderInterface
{
    public const PluginName = 'Magento';
}