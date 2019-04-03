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
 * @package     Magestore_Auction
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Auction\Model;

/**
 * Autobid Model
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Autobid extends \Magento\Framework\Model\AbstractModel
{
    const AUTOBID_DISABLE = 0;
    const AUTOBID_ENABLE = 1;
    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;

    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;

    /**
     * Bid constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Bid $resource
     * @param ResourceModel\Bid\Collection $resourceCollection
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param AuctionFactory $aution
     * @param BidderFactory $bidderFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Auction\Model\ResourceModel\Autobid $resource,
        \Magestore\Auction\Model\ResourceModel\Autobid\Collection $resourceCollection,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magestore\Auction\Model\AuctionFactory $aution,
        \Magestore\Auction\Model\BidderFactory $bidderFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_bidderFactory = $bidderFactory;
        $this->_auctionFactory = $aution;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_stdTimezone = $_stdTimezone;
        $this->_customerFactory = $_customerFactory;
    }

    /**
     * @param $auctionId
     * @param $customerId
     * @param $price
     * @return $this
     */
    public function createNewAutoBid($auctionId, $customerId, $price){
        $this->disableOldAutobid($auctionId,$customerId);
        $this->setAuctionId($auctionId)
            ->setCustomerId($customerId)
            ->setPrice($price)
            ->setCreatedTime($this->_stdTimezone->date()->format('Y-m-d H:i:s'))
            ->setStatus(self::AUTOBID_ENABLE);
        try{
            $this->save();
        }catch(\Exception $e){
        }
        return $this;
    }

    /**
     * @param $auctionId
     * @param $customerId
     */
    protected function disableOldAutobid($auctionId,$customerId){
        $autobids = $this->getCollection()
            ->addFieldToFilter('auction_id', $auctionId)
            ->addFieldToFilter('customer_id',$customerId)
            ->addFieldToFilter('status',self::AUTOBID_ENABLE);
        foreach($autobids as $_autobid){
            $_autobid->setData('status',self::AUTOBID_DISABLE)
                ->save();
        }
    }

    /**
     * @param $auctionId
     * @return bool|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastBid($auctionId){
        $bid = $this->getResourceCollection()
            ->addFieldToFilter('auction_id',$auctionId)
            ->setOrder('created_time','DESC')
            ->addFieldToFilter('status',['in'=>[
                self::AUTOBID_ENABLE
            ]])
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
        return $bid;
    }

    /**
     * @param \Magestore\Auction\Model\Auction $auction
     * @return \Magestore\Auction\Model\ResourceModel\Autobid\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOverAutobid($auction){
        return $this->getResourceCollection()
            ->addFieldToFilter('auction_id',$auction->getId())
            ->addFieldToFilter('price',['gt' => $auction->getCurrentPrice()])
            ->setOrder('price',\Magento\Framework\Data\Collection::SORT_ORDER_DESC)
            ->setOrder('created_time',\Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->addFieldToFilter('status',['in'=>[
                self::AUTOBID_ENABLE
            ]])
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
    }

    /**
     * @return \Magestore\Auction\Model\Auction
     */
    public function getAuction(){
        if(!$this->getData('auction')){
            $this->setData('auction',$this->_auctionFactory->create()->load($this->getAuctionId()));
        }
        return $this->getData('auction');
    }

    /**
     * @return string
     */
    public function getFormatedTime(){
        return $this->_stdTimezone->formatDateTime($this->getCreatedTime());
    }
    /**
     * @return string
     */
    public function getAuctionName(){
        return $this->getAuction()->getName();
    }
    /**
     * @return string
     */
    public function getPriceText(){
        return $this->_checkoutHelper->formatPrice($this->getPrice());
    }

    /**
     * @return array
     */
    public static function getStatusArray(){
        return [
            self::AUTOBID_DISABLE => __('Disabled'),
            self::AUTOBID_ENABLE => __('Enabled'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusLabel(){
        switch($this->getStatus()){
            case self::AUTOBID_DISABLE:
                return __('Disabled');
            case self::AUTOBID_ENABLE:
                return __('Enabled');
            default: return '';
        }
    }
}