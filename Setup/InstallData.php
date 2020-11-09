<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    PHPCuong
 * @package     PHPCuong_CustomerLogin
 * @copyright   Copyright (c) 2020-2021 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace PHPCuong\CustomerLogin\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $this->addCustomerAttribute($customerSetup, 'login_status', 1000, 'Blocked', '0', 'boolean', 'int');
        $this->addCustomerAttribute($customerSetup, 'reason', 1090, 'Reason', '0', 'select', 'varchar');
    }

    /**
     * Add the customization customer attribute
     *
     * @param CustomerSetup $customerSetup
     * @param string $customerAttributeCode
     * @param integer $sortOrder
     * @param string $attributeLable
     * @param string $default
     * @param string $inputType
     * @param string $type
     * @param string $backend
     * @return void
     */
    protected function addCustomerAttribute(
        $customerSetup,
        $customerAttributeCode,
        $sortOrder,
        $attributeLable,
        $default,
        $inputType,
        $type,
        $backend = ''
    ) {
        try {
            $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $boolean = true;
            // This is for the "login_status" customer attribute
            $source = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
            // This is for the "reason" customer attribute
            if ($customerAttributeCode == 'reason') {
                $source = 'PHPCuong\CustomerLogin\Model\Customer\Attribute\Source\Reason';
            }
            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $customerSetup->addAttribute(Customer::ENTITY, $customerAttributeCode, [
                'type' => $type,
                'label' => $attributeLable,
                'input' => $inputType,
                'required' => false,
                'visible' => $boolean,
                'user_defined' => true,
                'sort_order' => $sortOrder,
                'position' => $sortOrder,
                'system' => 0,
                'is_used_in_grid' => $boolean,
                'is_visible_in_grid' => $boolean,
                'is_html_allowed_on_front' => false,
                'visible_on_front' => $boolean,
                'source' => $source,
                'backend' => $backend,
                'default' => $default
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $customerAttributeCode)
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]);
            $attribute->save();
        } catch (\Exception $e) {}
    }
}
