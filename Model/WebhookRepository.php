<?php

namespace Conekta\Payments\Model;

use Conekta\Payments\Api\Data\ConektaSalesOrderInterface;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;

class WebhookRepository
{
    /**
     * @param OrderInterface $orderInterface
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param ConektaLogger $conektaLogger
     * @param ConektaSalesOrderInterface $conektaOrderSalesInterface
     */
    public function __construct(
        protected OrderInterface $orderInterface,
        protected InvoiceService $invoiceService,
        protected InvoiceSender $invoiceSender,
        protected Transaction $transaction,
        private ConektaLogger $conektaLogger,
        private ConektaSalesOrderInterface $conektaOrderSalesInterface
    ) {
    }

    /**
     * Find store order in request body.
     * If the keys['data']['object']['metadata']['order_id'] does not exist
     * in $body, throws an Exception
     * @param array[] $body
     * @return Order
     * @throws LocalizedException
     */
    public function findByMetadataOrderId(array $body): Order
    {
        if (! isset($body['data']['object'])
            || ! isset($body['data']['object']['id'])
        ) {
            throw new LocalizedException(__('Missing order information'));
        }
        $conektaOrderId = $body['data']['object']['id'];

        $this->conektaLogger->info('WebhookRepository :: findByMetadataOrderId started', [
            'order_id' => $conektaOrderId
        ]);

        $conetakSalesOrder = $this->conektaOrderSalesInterface->loadByConektaOrderId($conektaOrderId);

        $order = $this->orderInterface->loadByIncrementId($conetakSalesOrder->getIncrementOrderId());

        return $order;
    }

    /**
     * Finds order by metadata id passed in $body.
     * If the state of store order is Pending, set as CANCELED.
     *
     * If order not exists, throws an exception
     * @param array $body
     * @return void
     * @throws LocalizedException
     */
    public function expireOrder(array $body): void
    {
        $this->conektaLogger->info('WebhookRepository :: expireOrder started');

        $order = $this->findByMetadataOrderId($body);

        if (! $order->getId()) {
            throw new LocalizedException(__('We could not locate the order in the store'));
        }

        //Only update order status if is Pending
        if ($order->getState() === Order::STATE_PENDING_PAYMENT
            || $order->getState() === Order::STATE_PAYMENT_REVIEW
        ) {
            $order->setState(Order::STATE_CANCELED);
            $order->setStatus(Order::STATE_CANCELED);

            $order->addStatusHistoryComment('Order Expired')
                    ->setIsCustomerNotified(true);

            $order->save();
        }

        $this->conektaLogger->info('WebhookRepository :: orderExpiredProcess: Order has been Canceled');
    }

    /**
     * @param array $body
     * @return void
     * @throws LocalizedException
     */
    public function payOrder(array $body): void
    {
        $order = $this->findByMetadataOrderId($body);

        $charge = $body['data']['object'];
        if (! isset($charge['payment_status']) || $charge['payment_status'] !== 'paid') {
            throw new LocalizedException(__('Missing order information'));
        }

        if (! $order->getId()) {
            $message = 'The order does not exists';
            $this->conektaLogger->error(
                'WebhookRepository :: execute - ' . $message
            );
            throw new LocalizedException(__($message));
        }

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);

        $order->addStatusHistoryComment('Payment received successfully')
            ->setIsCustomerNotified(true);

        $order->save();
        $this->conektaLogger->info('WebhookRepository :: execute - Order status updated');

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->transaction->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();
        $this->conektaLogger->info('WebhookRepository :: execute - The invoice to be created');

        try {
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true)
                ->save();
            $this->conektaLogger->info(
                'WebhookRepository :: execute - Notified customer about invoice creation'
            );
        } catch (\Exception $e) {
            $this->conektaLogger->error(
                'WebhookRepository :: execute - We can\'t send the invoice email right now.'
            );
        }
    }
}
