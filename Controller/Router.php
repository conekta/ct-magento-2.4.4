<?php

namespace Conekta\Payments\Controller;

use Conekta\Payments\Helper\{Data, Data as ConektaHelper};
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;

/**
 * Class Router
 */
class Router implements RouterInterface
{
    protected ResponseInterface $_response;
    protected ActionFactory $actionFactory;
    private ConektaLogger $conektaLogger;
    private ConektaHelper $conektaHelper;

    /**
     * Router construct
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $_response
     * @param Data $conektaHelper
     * @param ConektaLogger $conektaLogger
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $_response,
        ConektaHelper $conektaHelper,
        ConektaLogger $conektaLogger
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $_response;
        $this->conektaHelper = $conektaHelper;
        $this->conektaLogger = $conektaLogger;
    }

    /**
     * Validate and Match
     *
     * @param RequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
