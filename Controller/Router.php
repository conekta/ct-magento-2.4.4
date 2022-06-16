<?php

namespace Conekta\Payments\Controller;

use Conekta\Payments\Helper\{Data, Data as ConektaHelper};
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\App\{ActionFactory, ActionInterface, RequestInterface, ResponseInterface, RouterInterface};
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $_response
     * @param Data $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        protected ActionFactory $actionFactory,
        protected ResponseInterface $_response,
        private ConektaHelper $conektaHelper,
        private ConektaLogger $conektaLogger
    ) {
    }

    /**
     * Validate and Match
     *
     * @param RequestInterface $request
     * @return void
     */
    public function match(RequestInterface $request): void
    {
        if ($request->getModuleName() === 'conekta') {
            return;
        }

        $pathRequest = trim($request->getPathInfo(), '/');

        $urlWebhook = $this->conektaHelper->getUrlWebhookOrDefault();
        $urlWebhook = trim($urlWebhook, '/');
        $pathWebhook = substr($urlWebhook, -strlen($pathRequest));

        //If paths are identical, then redirects to webhook controller
        if ($pathRequest === $pathWebhook) {
            $request->setModuleName('conekta')->setControllerName('webhook')->setActionName('index');
            $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $pathRequest);
        }
    }
}
