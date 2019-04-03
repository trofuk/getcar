<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Pdfinvoiceplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Auction\Controller\Index;
/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class Postauctionconfigs extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProductHelper;

    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magestore\Auction\Model\BidderFactory $bidderFactory
    ) {
        parent::__construct($context);
        $this->_catalogProductHelper = $catalogProductHelper;
        $this->_bidder = $bidderFactory;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        if(!$this->_bidder->create()->isLoggedIn()){
            $this->messageManager->addError(__('You are removed to the watch of this auction.'));
            return $this->redirect();
        }
        $bidder = $this->_bidder->create()->UpdateForCurrentBidder($this->getRequest()->getParams());
        if($bidder){
            $this->messageManager->addSuccess(__('You are saved your auction setting.'));
        }
        return $this->redirect();
    }


    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirect(){
        if($this->getRequest()->getParam('product_id')){
            $redirectUrl =  $this->_catalogProductHelper->getProductUrl($this->getRequest()->getParam('product_id'));
        }elseif($this->getRequest()->getParam('configpage'))
            $redirectUrl =  'auction/index/configs';
        else $redirectUrl =  '';
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }
}