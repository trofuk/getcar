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
namespace Magestore\Auction\Model\Plugin\Product;

class ListProduct
{
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * IsSaleAble constructor.
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     */
    public function __construct(
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {

        $this->_auctionFactory = $auctionFactory;
        $this->_request = $request;
        $this->_registry = $registry;
    }

    /**
     * @param \Magento\Catalog\Pricing\Price\BasePrice $object
     * @param $price
     * @return $this|float|int
     */
    public function afterGetProductPrice(\Magento\Catalog\Block\Product\ListProduct $object, $price)
    {
        if($this->_registry->registry('current_auction')) {
            return '<div data-product-id="'.$object->getProduct()->getId().'"></div>';
        }
        return $price;
    }



}