<?php

namespace Conekta\Payments\Helper;

use Conekta\Payments\Logger\Logger as ConektaLogger;
use Conekta\{Customer, Handler, ParameterValidationError, ProcessingError};
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\{LocalizedException, NoSuchEntityException};
use Magento\Framework\Module\ModuleListInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\{ScopeInterface, StoreManagerInterface};

class Data extends Util
{
    /**
     * Data constructor.
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param EncryptorInterface $encryptor
     * @param ProductMetadataInterface $productMetadata
     * @param ConektaLogger $conektaLogger
     * @param Customer $conektaCustomer
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param ProductRepository $productRepository
     * @param Escaper $escaper
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected Context $context,
        protected ModuleListInterface $moduleList,
        protected EncryptorInterface $encryptor,
        protected ProductMetadataInterface $productMetadata,
        protected ConektaLogger $conektaLogger,
        protected Customer $conektaCustomer,
        private CheckoutSession $checkoutSession,
        private CustomerSession $customerSession,
        private ProductRepository $productRepository,
        private Escaper $escaper,
        protected CartRepositoryInterface $cartRepository,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrencyCode(): string
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param $area
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigData($area, $field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            sprintf('payment/%s/%s', $area, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function getModuleVersion()
    {
        return $this->moduleList->getOne($this->_getModuleName())['setup_version'];
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        $sandboxMode = $this->getConfigData('conekta/conekta_global', 'sandbox_mode');

        $privateKey = $this->encryptor->decrypt($this->getConfigData(
            'conekta/conekta_global',
            'test_private_api_key'
        ));

        if (! $sandboxMode) {
            $privateKey = $this->encryptor->decrypt($this->getConfigData(
                'conekta/conekta_global',
                'live_private_api_key'
            ));
        }

        return $privateKey;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        $sandboxMode = $this->getConfigData('conekta/conekta_global', 'sandbox_mode');
        $publicKey = $this->getConfigData('conekta/conekta_global', 'test_public_api_key');

        if (! $sandboxMode) {
            $publicKey = $this->getConfigData('conekta/conekta_global', 'live_public_api_key');
        }

        return $publicKey;
    }

    /**
     * @return mixed
     */
    public function getApiVersion()
    {
        return $this->scopeConfig->getValue(
            'conekta/global/api_version',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function pluginType()
    {
        return $this->scopeConfig->getValue(
            'conekta/global/plugin_type',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function pluginVersion()
    {
        return $this->scopeConfig->getValue(
            'conekta/global/plugin_version',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getMageVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @param $orderParams
     * @param $chargeParams
     * @return void
     */
    public function deleteSavedCard($orderParams, $chargeParams): void
    {
        $this->conektaLogger->info('deleteSavedCard: Remove Decline Card From Conekta Customer');

        try {
            $paymentSourceId = '';
            if (isset($chargeParams['payment_method']['payment_source_id'])) {
                $paymentSourceId = $chargeParams['payment_method']['payment_source_id'];
            }

            $customerId = '';
            if (isset($orderParams['customer_info']['customer_id'])) {
                $customerId = $orderParams['customer_info']['customer_id'];
            }

            if ($customerId && $paymentSourceId) {
                $customer = $this->conektaCustomer->find($customerId);
                $customer->deletePaymentSourceById($paymentSourceId);
            }
        } catch (ProcessingError|ParameterValidationError|Handler $error) {
            $this->conektaLogger->info($error->getMessage());
        }
    }

    /**
     * @param $metadataPath
     * @return array
     */
    public function getMetadataAttributes($metadataPath): array
    {
        $attributes = $this->getConfigData('conekta/conekta_global', $metadataPath);
        $attributesArray = explode(',', $attributes);

        return $attributesArray;
    }

    /**
     * @return bool
     */
    public function is3DSEnabled(): bool
    {
        return (bool)$this->getConfigData('conekta_cc', 'iframe_enabled');
    }

    /**
     * @return bool
     */
    public function isSaveCardEnabled(): bool
    {
        return (bool)$this->getConfigData('conekta_cc', 'enable_saved_card');
    }

    /**
     * @return bool
     */
    public function isCreditCardEnabled(): bool
    {
        return  (bool)$this->getConfigData('conekta_cc', 'active');
    }

    /**
     * @return bool
     */
    public function isOxxoEnabled(): bool
    {
        return  (bool)$this->getConfigData('conekta_oxxo', 'active');
    }

    /**
     * @return bool
     */
    public function isSpeiEnabled(): bool
    {
        return  (bool)$this->getConfigData('conekta_spei', 'active');
    }

    /**
     * @return int
     */
    public function getExpiredAt(): int
    {
        $timeFormat = $this->getConfigData('conekta/conekta_global', 'days_or_hours');
        $expirationValue = null;
        $expirationUnit = null;

        //hours expiration disabled temporaly
        if (! $timeFormat && false) {
            $expirationValue = $this->getConfigData('conekta/conekta_global', 'expiry_hours');
            $expirationUnit = 'hours';
        } else {
            $expirationValue = $this->getConfigData('conekta/conekta_global', 'expiry_days');
            $expirationUnit = 'days';
        }

        if (empty($expirationValue)) {
            $expirationValue = 3;
        }

        $expiryDate = strtotime('+' . $expirationValue . ' ' . $expirationUnit);

        return $expiryDate;
    }

    /**
     * @param $array
     * @return string
     */
    private function customFormat($array): string
    {
        $ret = '';
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                if (count($item) == 0) {
                    $item = 'null';
                    $ret .= $key . ' : ' . $item . ' | ';
                    continue;
                }
                foreach ($item as $k => $i) {
                    $ret .= $key . '_' . $k . ' : ' . $i . ' | ';
                }
            } else {
                if ($item == '') {
                    $item = 'null';
                } elseif ($key == 'has_options' || $key == 'new') {
                    if ($item == '0') {
                        $item = 'no';
                    } elseif ($item == '1') {
                        $item = 'yes';
                    }
                }
                $ret .= $key . ' : ' . $item . ' | ';
            }
        }
        $ret = substr($ret, 0, 0 - strlen(' | '));
        return $ret;
    }

    /**
     * @param $items
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getMetadataAttributesConekta($items): array
    {
        $productAttributes = $this->getMetadataAttributes('metadata_additional_products');
        $request = [];
        if (count($productAttributes) > 0 && ! empty($productAttributes[0])) {
            foreach ($items as $item) {
                if ($item->getProductType() != 'configurable') {
                    $productValues = [];
                    $productId = $item->getProductId();
                    $product = $this->productRepository->getById($productId);
                    foreach ($productAttributes as $attr) {
                        $productValues[$attr] = $this->removeSpecialCharacter($product->getData($attr));
                    }
                    $request['Product-' . $productId] = $this->customFormat($productValues);
                }
            }
        }

        $orderAttributes = $this->getMetadataAttributes('metadata_additional_order');

        if (count($orderAttributes) > 0 && ! empty($orderAttributes[0])) {
            foreach ($orderAttributes as $attr) {
                $quoteValue = $this->checkoutSession->getQuote()->getData($attr);
                if ($quoteValue == null) {
                    $request[$attr] = 'null';
                } elseif (is_array($quoteValue)) {
                    $request[$attr] = $this->customFormat($quoteValue);
                } elseif (! is_string($quoteValue)) {
                    if ($attr == 'customer_gender') {
                        $customer = $this->customerSession->getCustomer();
                        $customerDataGender = $customer->getData('gender');
                        $gender = $customer->getAttribute('gender')->getSource()->getOptionText($customerDataGender);
                        $request[$attr] = strtolower($gender);
                    } elseif ($attr == 'is_changed') {
                        if ($quoteValue == 0) {
                            $request[$attr] = 'no';
                        } elseif ($quoteValue == 1) {
                            $request[$attr] = 'yes';
                        }
                    } else {
                        $request[$attr] = (string)$quoteValue;
                    }
                } else {
                    if ($attr == 'is_active'
                        || $attr == 'is_virtual'
                        || $attr == 'is_multi_shipping'
                        || $attr == 'customer_is_guest'
                        || $attr == 'is_persistent'
                    ) {
                        if ($quoteValue == '0') {
                            $request[$attr] = 'no';
                        } elseif ($quoteValue == '1') {
                            $request[$attr] = 'yes';
                        }
                    } else {
                        $request[$attr] = $quoteValue;
                    }
                }
            }
        }
        return $request;
    }

    /**
     * @return array
     */
    public function getMagentoMetadata(): array
    {
        return [
            'plugin'                 => 'Magento',
            'plugin_version'         => $this->getMageVersion(),
            'plugin_conekta_version' => $this->pluginVersion()
        ];
    }

    /**
     * @param $items
     * @param bool $isQuoteItem
     * @return array
     */
    public function getLineItems($items, bool $isQuoteItem = true): array
    {
        $version = (int)str_replace('.', '', $this->getMageVersion());
        $request = [];
        $quantityMethod = $isQuoteItem ? 'getQty' : 'getQtyOrdered';
        foreach ($items as $itemId => $item) {
            if ($version > 233) {
                if ($item->getProductType() != 'bundle' && $item->getProductType() != 'configurable') {
                    $price = $item->getPrice();
                    $qty = (int)$item->{$quantityMethod}();
                    if (! empty($item->getParentItem())) {
                        $parent = $item->getParentItem();

                        if ($parent->getProductType() == 'configurable') {
                            $price = $item->getParentItem()->getPrice();
                            $qty = (int)$item->getParentItem()->{$quantityMethod}();
                        } elseif ($parent->getProductType() == 'bundle' && $isQuoteItem) {
                            //If it is a quote item, then qty of item has not been calculate yet
                            $qty = $qty * (int)$item->getParentItem()->{$quantityMethod}();
                        }
                    }

                    $name = $this->removeSpecialCharacter($item->getName());
                    $description = $this->removeSpecialCharacter(
                        $this->escaper->escapeHtml($item->getName() . ' - ' . $item->getSku())
                    );
                    $description = substr($description, 0, 250);

                    $request[] = [
                        'name'        => $name,
                        'sku'         => $this->removeSpecialCharacter($item->getSku()),
                        'unit_price'  => $this->convertToApiPrice($price),
                        'description' => $description,
                        'quantity'    => $qty,
                        'tags'        => [
                            $item->getProductType()
                        ]
                    ];
                }
            } else {
                if ($item->getProductType() != 'bundle' && $item->getPrice() > 0) {
                    $request[] = [
                        'name'        => $item->getName(),
                        'sku'         => $item->getSku(),
                        'unit_price'  => $this->convertToApiPrice($item->getPrice()),
                        'description' => $this->_escaper->escapeHtml($item->getName() . ' - ' . $item->getSku()),
                        'quantity'    => (int)($item->{$quantityMethod}()),
                        'tags'        => [
                            $item->getProductType()
                        ]
                    ];
                }
            }
        }
        return $request;
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getUrlWebhookOrDefault()
    {
        $urlWebhook = $this->getConfigData('conekta/conekta_global', 'conekta_webhook');
        if (empty($urlWebhook)) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $urlWebhook = $baseUrl . 'conekta/webhook/listener';
        }
        return $urlWebhook;
    }

    /**
     * @param $quoteId
     * @param bool $isCheckout
     * @return array
     * @throws NoSuchEntityException
     */
    public function getShippingLines($quoteId, bool $isCheckout = true): array
    {
        $quote = $this->cartRepository->get($quoteId);
        $shippingAddress = $quote->getShippingAddress();

        $shippingLines = [];

        if ($quote->getIsVirtual()) {
            $shippingLines[] = ['amount' => 0 ];
        } elseif ($shippingAddress) {
            $shippingLine['amount'] = $this->convertToApiPrice($shippingAddress->getShippingAmount());

            //Chekout orders doesn't allow method and carrier parameters
            if (! $isCheckout) {
                $shippingLine['method'] = $shippingAddress->getShippingMethod();
                $shippingLine['carrier'] = $shippingAddress->getShippingDescription();
            }

            $shippingLines[] = $shippingLine;
        }

        return $shippingLines;
    }

    /**
     * @param $quoteId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getShippingContact($quoteId): array
    {
        $quote = $this->cartRepository->get($quoteId);
        $address = null;

        $shippingContact = [];

        if ($quote->getIsVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        if ($address) {
            $phone = $this->removePhoneSpecialCharacter($address->getTelephone());

            $shippingContact = [
                'receiver' => $this->getCustomerName($address),
                'phone'    => $phone,
                'address'  => [
                    'city'        => $address->getCity(),
                    'state'       => $address->getRegionCode(),
                    'country'     => $address->getCountryId(),
                    'postal_code' => $this->onlyNumbers($address->getPostcode()),
                    'phone'       => $phone,
                    'email'       => $address->getEmail()
                ]
            ];

            $street = $address->getStreet();
            $streetStr = isset($street[0]) ? $street[0] : 'NO STREET';
            $shippingContact['address']['street1'] = $this->removeSpecialCharacter($streetStr);
            if (isset($street[1])) {
                $shippingContact['address']['street2'] = $this->removeSpecialCharacter($street[1]);
            }
        }
        return $shippingContact;
    }

    /**
     * @param $shipping
     * @return array|string|null
     */
    public function getCustomerName($shipping)
    {
        $customerName = sprintf(
            '%s %s %s',
            $shipping->getFirstname(),
            $shipping->getMiddlename(),
            $shipping->getLastname()
        );

        return $this->removeNameSpecialCharacter($customerName);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDiscountLines(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $totalDiscount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        $totalDiscount = abs(round($totalDiscount, 2));

        $discountLines = [];
        if (! empty($totalDiscount)) {
            $totalDiscount = $this->convertToApiPrice($totalDiscount);
            $discountLine['code'] = 'Discounts';
            $discountLine['type'] = 'coupon';
            $discountLine['amount'] = $totalDiscount;
            $discountLines[] = $discountLine;
        }

        return $discountLines;
    }

    /**
     * @param $items
     * @return array
     */
    public function getTaxLines($items): array
    {
        $taxLines = [];
        $ctr_amount = 0;
        foreach ($items as $item) {
            if ($item->getProductType() != 'bundle' && $item->getTaxAmount() > 0) {
                $ctr_amount += $this->convertToApiPrice($item->getTaxAmount());
            }
        }

        $taxLines[] = [
            'description' => 'Tax',
            'amount'      => $ctr_amount
        ];

        return $taxLines;
    }
}
