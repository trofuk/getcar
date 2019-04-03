<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Auction\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectToLogin extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProductHelper;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Catalog\Helper\Product $catalogProductHelper
    ) {
        $this->session = $customerSession;
        $this->_catalogProductHelper = $catalogProductHelper;
        parent::__construct($context);
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $productUrl = $this->_catalogProductHelper->getProductUrl($this->getRequest()->getParam('product_id'));
        $this->session->setBeforeAuthUrl($productUrl);
        if($this->session->isLoggedIn()){
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($productUrl);
            return $resultRedirect;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }
}
