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

namespace PHPCuong\CustomerLogin\Model\Customer\Attribute\Source;

use Magento\Store\Model\ScopeInterface;

class Reason extends \Magento\Eav\Model\Entity\Attribute\Source\Table 
    implements \Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface
{
    const XML_REASONS_LIST = 'customer/login_status/reasons';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serialize;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Framework\Escaper|null $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Framework\Escaper $escaper = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serialize = $serialize;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @return []
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->getReasonsList();
        }

        return $this->_options;
    }

    /**
     * @return []
     */
    private function getReasonsList()
    {
        $options = [];
        $options[] = [
            'value' => '0',
            'label' => __('-- Please select a reason --'),
        ];

        $reasonsList = $this->scopeConfig->getValue(self::XML_REASONS_LIST, ScopeInterface::SCOPE_STORE);

        if (!empty($reasonsList)) {
            $_reasonsList = $this->serialize->unserialize($reasonsList);
            foreach ($_reasonsList as $reason) {
                $options[] = [
                    'value' => $reason['id'],
                    'label' => $reason['reason'],
                ];
            }
        }

        return $options;
    }

    /**
     * @param string $value
     * @return string
     */
    public function getMessage($value)
    {
        $reasonsList = $this->getReasonsList();
        foreach ($reasonsList as $reason) {
            if ($value == $reason['value'] && $value != '0') {
                return $reason['label'];
            }
        }
        return __('Please contact us for details.');
    }
}
