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
use Magento\Customer\Model\Session;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class Removefromwatchlist extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProductHelper;
    /**
     * @var \Magestore\Auction\Model\Auction
     */
    protected $_auctionFactory;
    /**
     * @var Session
     */
    protected $session;
    /**
     * ViewAuction constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Auction\Model\Auction $auctionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Auction\Model\AuctionFactory $auction,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->_auctionFactory = $auction;
        $this->_catalogProductHelper = $catalogProductHelper;
        $this->session = $customerSession;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $auction = $this->_auctionFactory->create()->load($this->getRequest()->getParam('auction_id'));
        if(!$auction->getId()||!$this->session->isLoggedIn()){
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->getRedirectUrl());
            return $resultRedirect;
        }
        $auction->removeFromWatchList($this->session->getCustomerId());
        $this->messageManager->addSuccess( __('You are removed to the watch of this auction.') );
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getRedirectUrl());
        return $resultRedirect;
    }

    public function getRedirectUrl(){
        if($this->getRequest()->getParam('product_id')){
            return $this->_catalogProductHelper->getProductUrl($this->getRequest()->getParam('product_id'));
        }elseif($this->getRequest()->getParam('watchlist_page'))
            return 'auction/index/watchlist';
        else return '';
    }
}