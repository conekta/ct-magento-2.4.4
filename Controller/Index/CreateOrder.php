<?php

namespace Conekta\Payments\Controller\Index;

use Conekta\Payments\Api\EmbedFormRepositoryInterface;
use Conekta\Payments\Exception\ConektaException;
use Conekta\Payments\Helper\ConektaOrder;
use Conekta\Payments\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\{Json, JsonFactory};
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class CreateOrder extends Action implements HttpPostActionInterface
{
    protected Logger $logger;
    protected ConektaOrder $conektaOrderHelper;
    protected JsonFactory $resultJsonFactory;
    protected PageFactory $resultPageFactory;
    private Session $checkoutSession;
    private EmbedFormRepositoryInterface $embedFormRepository;

    /**
     * CreateOrder constructor
     *
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
        PageFactory  $resultPageFactory,
        JsonFactory  $resultJsonFactory,
        ConektaOrder $conektaOrderHelper,
        Logger       $logger,
        EmbedFormRepositoryInterface $embedFormRepository,
        Session        $checkoutSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->conektaOrderHelper = $conektaOrderHelper;
        $this->logger = $logger;
        $this->embedFormRepository = $embedFormRepository;
        $this->checkoutSession = $checkoutSession;
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
