<?php

namespace Conekta\Payments\Helper;

use Conekta\Conekta;
use Conekta\Customer as ConektaCustomer;
use Conekta\Handler;
use Conekta\Order as ConektaOrderApi;
use Conekta\Payments\Exception\ConektaException;
use Conekta\Payments\Helper\Data as ConektaHelper;
use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\Payments\Model\Ui\CreditCard\ConfigProvider;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Quote\Model\Quote;

class ConektaOrder extends Util
{
    /**
     * ConektaOrder constructor.
     * @param Context $context
     * @param ConektaHelper $conektaHelper
     * @param ConektaLogger $conektaLogger
     * @param ConektaCustomer $conektaCustomer
     * @param ConektaOrderApi $conektaOrderApi
     * @param CustomerSession $customerSession
     * @param Session $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigProvider $conektaConfigProvider
     */
    public function __construct(
        protected Context $context,
        protected ConektaHelper $conektaHelper,
        protected ConektaLogger $conektaLogger,
        protected ConektaCustomer $conektaCustomer,
        protected ConektaOrderApi $conektaOrderApi,
        protected CustomerSession $customerSession,
        protected Session $checkoutSession,
        protected CustomerRepositoryInterface $customerRepository,
        protected ConfigProvider $conektaConfigProvider
    ) {
        parent::__construct($context);
    }

    /**
     * @param $guestEmail
     * @return array
     * @throws ConektaException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputMismatchException
     */
    public function generateOrderParams($guestEmail): array
    {
        $this->conektaLogger->info('ConektaOrder.generateOrderParams init', []);

        Conekta::setApiKey($this->conektaHelper->getPrivateKey());
        Conekta::setApiVersion("2.0.0");
        $customerRequest = [];
        try {
            $customer = $this->customerSession->getCustomer();
            $customerApi = null;
            $conektaCustomerId = $customer->getConektaCustomerId();
            
            try {
                $customerApi = $this->conektaCustomer->find($conektaCustomerId);
            } catch (Exception $error) {
                $this->conektaLogger->info('Create Order. Find Customer: ' . $error->getMessage());
                $conektaCustomerId = '';
            }

            //Customer Info for API
            $billingAddress = $this->getQuote()->getBillingAddress();
            $customerId = $customer->getId();
            if ($customerId) {
                //name without numbers
                $customerRequest['name'] = $customer->getName();
                $customerRequest['email'] = $customer->getEmail();
            } else {
                //name without numbers
                $customerRequest['name'] = $billingAddress->getName();
                $customerRequest['email'] = $guestEmail;
            }
            $customerRequest['name'] = $this->removeNameSpecialCharacter($customerRequest['name']);
            $customerRequest['phone'] = $this->removePhoneSpecialCharacter(
                $billingAddress->getTelephone()
            );
            
            if (strlen($customerRequest['phone']) < 10) {
                $this->conektaLogger->info('Helper.CreateOrder phone validation error', $customerRequest);
                throw new ConektaException(__('Télefono no válido. 
                    El télefono debe tener al menos 10 carácteres. 
                    Los caracteres especiales se desestimaran, solo se puede ingresar como 
                    primer carácter especial: +'));
            }
            
            if (empty($conektaCustomerId)) {
                $conektaAPI = $this->conektaCustomer->create($customerRequest);
                $conektaCustomerId = $conektaAPI->id;
                if ($customerId) {
                    $customer = $this->customerRepository->getById($customerId);
                    $customer->setCustomAttribute('conekta_customer_id', $conektaCustomerId);
                    $this->customerRepository->save($customer);
                }
                
            } else {
                //If cutomer API exists, always update error
                $customerApi->update($customerRequest);
            }
        } catch (Handler $error) {
            $this->conektaLogger->info($error->getMessage(), $customerRequest);
            throw new ConektaException(__($error->getMessage()));
        }
        $orderItems = $this->getQuote()->getAllItems();

        $validOrderWithCheckout = [];
        $validOrderWithCheckout['line_items'] = $this->conektaHelper->getLineItems($orderItems);
        $validOrderWithCheckout['discount_lines'] = $this->conektaHelper->getDiscountLines();
        $validOrderWithCheckout['tax_lines'] = $this->conektaHelper->getTaxLines($orderItems);
        $validOrderWithCheckout['shipping_lines'] = $this->conektaHelper->getShippingLines(
            $this->getQuote()->getId()
        );

        //always needs shipping due to api does not provide info about merchant type (dropshipping, virtual)
        $needsShippingContact = !$this->getQuote()->getIsVirtual() || true;
        if ($needsShippingContact) {
            $validOrderWithCheckout['shipping_contact'] = $this->conektaHelper->getShippingContact(
                $this->getQuote()->getId()
            );
        }
        
        $validOrderWithCheckout['customer_info'] = [
            'customer_id' => $conektaCustomerId
        ];
        
        $threeDsEnabled =  $this->conektaHelper->is3DSEnabled();
        $saveCardEnabled = $this->conektaHelper->isSaveCardEnabled() &&
            $customerId;
        $installments = $this->getMonthlyInstallments();
        $validOrderWithCheckout['checkout']    = [
            'allowed_payment_methods'      => $this->getAllowedPaymentMethods(),
            'monthly_installments_enabled' => (bool)$installments['active_installments'],
            'monthly_installments_options' => $installments['monthly_installments'],
            'on_demand_enabled'            => $saveCardEnabled,
            'force_3ds_flow'               => $threeDsEnabled,
            'expires_at'                   => $this->conektaHelper->getExpiredAt(),
            'needs_shipping_contact'       => $needsShippingContact
        ];
        $validOrderWithCheckout['currency']= $this->conektaHelper->getCurrencyCode();
        $validOrderWithCheckout['metadata'] = $this->getMetadataOrder($orderItems);
        
        return $validOrderWithCheckout;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getMonthlyInstallments(): array
    {
        $result = [];
        $isInstallmentsAvilable = 1;
        $quote = $this->getQuote();
        $total = $quote->getGrandTotal();
        $active_monthly_installments = $this->conektaHelper->getConfigData(
            'conekta/conekta_creditcard',
            'active_monthly_installments'
        );
        if ($active_monthly_installments) {
            $minimumAmountMonthlyInstallments = $this->conektaConfigProvider->getMinimumAmountMonthlyInstallments();
            if ((int)$minimumAmountMonthlyInstallments < (int)$total) {
                $months = explode(
                    ',',
                    $this->conektaHelper->getConfigData('conekta_cc', 'monthly_installments')
                );
                foreach ($months as $k => $v) {
                    if ((int)$total < ($v * 100)) {
                        unset($months[$k]);
                    } else {
                        $months[$k] = (int) $months[$k];
                    }
                }
                $result['active_installments'] = (int)!empty($months);
                $result['monthly_installments'] = $months;
            } else {
                $isInstallmentsAvilable = (int)false;
            }
        } else {
            $isInstallmentsAvilable = (int)false;
        }
        if (! $isInstallmentsAvilable) {
            $result['active_installments'] = (int)false;
            $result['monthly_installments'] = [];
        }
        return $result;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllowedPaymentMethods(): array
    {
        $methods = [];

        if ($this->conektaHelper->isCreditCardEnabled()) {
            $methods[] = 'card';
        }

        $total = $this->getQuote()->getSubtotal();
        if ($this->conektaHelper->isOxxoEnabled() &&
            $total <= 10000
        ) {
            $methods[] = 'cash';
        }
        if ($this->conektaHelper->isSpeiEnabled()) {
            $methods[] = 'bank_transfer';
        }
        return $methods;
    }

    /**
     * Get active quote
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote(): Quote
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteId(): array
    {
        $quote = $this->getQuote();
        $quoteId = $quote->getId();

        return ['quote_id' => $quoteId];
    }

    /**
     * @param $orderItems
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getMetadataOrder($orderItems): array
    {
        return array_merge(
            $this->conektaHelper->getMagentoMetadata(),
            ['quote_id' => $this->getQuote()->getId()],
            $this->conektaHelper->getMetadataAttributesConekta($orderItems)
        );
    }
}
