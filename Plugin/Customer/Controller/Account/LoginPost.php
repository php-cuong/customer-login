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

namespace PHPCuong\CustomerLogin\Plugin\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

class LoginPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * Login post action
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject
    ) {
        if ($this->request->isPost() && !$this->session->isLoggedIn()) {
            $login = $this->request->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    if ($customer->getId()) {
                        $loginStatus = $customer->getCustomAttribute('login_status');
                        // If the customer is blocked, 1 is locked, 0 is unlocked
                        if ($loginStatus && $loginStatus->getValue() == '1') {
                            // Display the reason
                            // You can create a customization message here
                            $this->messageManager->addError(
                                __('Your account is blocked for the security reason, please contact us for details.')
                            );
                            $resultRedirect = $this->responseFactory->create();
                            // Redirect to the customer login page
                            $resultRedirect->setRedirect($this->url->getUrl('customer/account/login'))->sendResponse('200');
                            exit();
                        }
                    }
                } catch (\Exception $e) {}
            }
        }
    }
}
