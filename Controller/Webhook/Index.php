<?php

namespace Conekta\Payments\Controller\Webhook;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\Payments\Model\WebhookRepository;
use Exception;
use Magento\Framework\App\Action\{Action, Context};
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\{CsrfAwareActionInterface, RequestInterface, ResponseInterface};
use Magento\Framework\Controller\Result\{JsonFactory, RawFactory};
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Webapi\Response;
use Magento\Payment\Model\Method\Logger;

class Index extends Action implements CsrfAwareActionInterface
{
    public const EVENT_WEBHOOK_PING = 'webhook_ping';
    public const EVENT_ORDER_CREATED = 'order.created';
    public const EVENT_ORDER_PENDING_PAYMENT = 'order.pending_payment';
    public const EVENT_ORDER_PAID = 'order.paid';
    public const EVENT_ORDER_EXPIRED = 'order.expired';

    public function __construct(
        Context $context,
        protected JsonFactory $resultJsonFactory,
        protected RawFactory $resultRawFactory,
        protected Data $helper,
        private Logger $logger,
        private ConektaLogger $conektaLogger,
        private WebhookRepository $webhookRepository
    ) {
        parent::__construct($context);
    }

    /** * @inheritDoc */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
    /** * @inheritDoc */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @return int|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $this->conektaLogger->info('Controller Index :: execute');

        $response = Response::STATUS_CODE_200;

        try {
            $resultRaw = $this->resultRawFactory->create();

            $body = $this->helper->jsonDecode($this->getRequest()->getContent());

            if (! $body || $this->getRequest()->getMethod() !== 'POST') {
                return Response::STATUS_CODE_400;
            }

            $event = $body['type'];

            $this->conektaLogger->info('Controller Index :: execute body json ', ['event' => $event]);

            switch ($event) {
                case self::EVENT_WEBHOOK_PING:
                    break;
                case self::EVENT_ORDER_CREATED:
                case self::EVENT_ORDER_PENDING_PAYMENT:
                    $order = $this->webhookRepository->findByMetadataOrderId($body);
                    if (! $order->getId()) {
                        $response = Response::STATUS_CODE_400;
                    }
                    break;
                case self::EVENT_ORDER_PAID:
                    $this->webhookRepository->payOrder($body);
                    break;
                case self::EVENT_ORDER_EXPIRED:
                    $this->webhookRepository->expireOrder($body);
                    break;
            }
        } catch (Exception $e) {
            $this->conektaLogger->error('Controller Index :: ' . $e->getMessage());
            $response = Response::STATUS_CODE_400;
        }

        return $resultRaw->setHttpResponseCode($response);
    }
}
