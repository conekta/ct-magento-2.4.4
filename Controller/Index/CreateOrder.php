<?php

namespace Conekta\Payments\Controller\Index;

use Conekta\Payments\Api\EmbedFormRepositoryInterface;
use Conekta\Payments\Exception\ConektaException;
use Conekta\Payments\Helper\ConektaOrder;
use Conekta\Payments\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\{Action, Context, HttpPostActionInterface};
use Magento\Framework\Controller\Result\{Json, JsonFactory};
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class CreateOrder extends Action implements HttpPostActionInterface
{
    /**
     * CreateOrder constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param ConektaOrder $conektaOrderHelper
     * @param Logger $logger
     * @param EmbedFormRepositoryInterface $embedFormRepository
     * @param Session $checkoutSession
     */
    public function __construct(
        Context                $context,
        protected PageFactory  $resultPageFactory,
        protected JsonFactory  $resultJsonFactory,
        protected ConektaOrder $conektaOrderHelper,
        protected Logger       $logger,
        private                EmbedFormRepositoryInterface $embedFormRepository,
        private Session        $checkoutSession
    ) {
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return Json|ResultInterface
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->isXmlHttpRequest();
        $response = [];

        $resultJson = $this->resultJsonFactory->create();
        $orderParams = [];
        if ($isAjax) {
            try {
                /** @var Json $resultJson */
                $data = $this->getRequest()->getPostValue();
                $guestEmail = $data['guestEmail'];

                //generate order params
                $orderParams = $this->conektaOrderHelper->generateOrderParams($guestEmail);

                $quoteSession = $this->checkoutSession->getQuote();

                //genrates checkout form
                $order = (array)$this->embedFormRepository->generate(
                    $quoteSession->getId(),
                    $orderParams,
                    $quoteSession->getSubtotal()
                );

                $response['checkout_id'] = $order['checkout']['id'];
            } catch (\Exception $e) {
                $errorMessage = 'Ha ocurrido un error inesperado. Notifique al dueño de la tienda.' . $e->getMessage();
                if ($e instanceof ConektaException) {
                    $errorMessage = $e->getMessage();
                } else {
                    $this->logger->critical($e, $orderParams);
                }

                $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
                $response['error_message'] = $errorMessage;
            }
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
