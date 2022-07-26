<?php

namespace Conekta\Payments\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\{DataPatchInterface, PatchInterface};

class AddCustomerConektaAttr implements DataPatchInterface
{
    /**
     * AddCustomerErpCustomerIdAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected CustomerSetupFactory $customerSetupFactory
    ) {
    }

    /**
     * Get array of patches that have to be executed prior to this.
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - than under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply(): void
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'conekta_customer_id',
            [
                'type'       => 'varchar',
                'label'      => 'Conekta Customer Id',
                'input'      => 'text',
                'required'   => false,
                'sort_order' => 87,
                'visible'    => true,
                'global'     => ScopedAttributeInterface::SCOPE_STORE,
                'system'     => 0
            ]
        );
        $erpAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'conekta_customer_id');
        $erpAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer']
        );
        $erpAttribute->save();
    }
}
