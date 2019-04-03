<?php

/**
 * Magestore.
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
 * @package     Magestore_Auction
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Auction\Model\Plugin\Catalog;

class ProductValue
{
    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_auctionConfig;

    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_autionFactory;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_productConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * ProductValue constructor.
     * @param \Magestore\Auction\Model\SystemConfig $auctionConfig
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magestore\Auction\Model\BidderFactory $bidderFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productConfig
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magestore\Auction\Model\SystemConfig $auctionConfig,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Checkout\Model\Session $session,
        \Magestore\Auction\Model\BidderFactory $bidderFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productConfig,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_auctionConfig = $auctionConfig;
        $this->_autionFactory = $auctionFactory;
        $this->_checkoutSession = $session;
        $this->_bidderFactory = $bidderFactory;
        $this->_productConfig = $productConfig;
        $this->_request = $request;

    }

    protected function _hasAuctionItem($bidId){
        $items = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach($items as $_item){
            if($_item->getOptionByCode('bid_id')&&$_item->getOptionByCode('bid_id')->getValue()==$bidId){
                return true;
            };
        }

        return false;
    }
    /**
     * @param \Magento\Catalog\Pricing\Price\BasePrice $object
     * @param $price
     * @return $this|float|int
     */
    public function afterGetValue(\Magento\Catalog\Pricing\Price\BasePrice $object, $price)
    {
        $links = $this->_request->getRouteName() . $this->_request->getControllerName() . $this->_request->getActionName();
        if ($links != 'catalogcategoryview' && $links != 'catalogsearchresultindex') {
            $bidder = $this->_bidderFactory->create();
            if (!$bidder->isLoggedIn()) {
                return $price;
            }
            $bidder = $bidder->getCurrentBidder();
            $productId = $object->getProduct()->getId();
            $auction = $bidder->getWonInfo($productId);
            if ($auction->getBidId() && !$this->_hasAuctionItem($auction->getBidId())) {
                return $auction->getCurrentPrice();
            }
            $productIds = $this->_productConfig->getParentIdsByChild($productId);
            foreach ($productIds as $_productId) {
                $auction = $bidder->getWonInfo($_productId);
                if ($auction->getBidId() && !$this->_hasAuctionItem($auction->getBidId())) {
                    return $auction->getCurrentPrice();
                }
            }
            return $price;
        }
        return $price;
    }



}