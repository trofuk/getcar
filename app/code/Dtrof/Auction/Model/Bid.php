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
 * Bid Model
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Bid extends \Magento\Framework\Model\AbstractModel
{
    const BID_OVERED = 1;
    const BID_ENABLE = 3;
    const BID_WINNER = 4;
    const BID_LOSER = 0;
    const BID_WON_AND_BOUGHT = 2;
    const BID_CANCELED = -1;

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
     * @var \Magestore\Auction\Helper\Email
     */
    protected $_email;
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
        \Magestore\Auction\Model\ResourceModel\Bid $resource,
        \Magestore\Auction\Model\ResourceModel\Bid\Collection $resourceCollection,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magestore\Auction\Model\AuctionFactory $aution,
        \Magestore\Auction\Model\BidderFactory $bidderFactory,
        \Magestore\Auction\Helper\Email $email
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
        $this->_email = $email;
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
            ->setOrder('price','DESC')
            ->addFieldToFilter('status',['in'=>[
                self::BID_ENABLE,
                self::BID_WINNER
            ]])
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
        return $bid;
    }

    /**
     * @param $auctionId
     * @param $customerId
     * @param $price
     * @return $this,
     */
    public function createNewBid($auction, $customerId, $price,$autobid_id = 0){
        $oldBid = $this->updateOverBid($auction->getId());
        $newBid = $this->setAuctionId($auction->getId())
            ->setCustomerId($customerId)
            ->setPrice($price)
            ->setAutobidId($autobid_id)
            ->setCreatedTime(date('Y-m-d H:i:s'))
            ->setStatus(self::BID_ENABLE);
        try{
            $this->save();
        }catch(\Exception $e){
            $this->messageManager->addError($e->getMessage());
            $this->messageManager->addException($e, __('Something went wrong while place your bid.'));
        }
        if($oldBid){
            $bidder = $this->_bidderFactory->create()->loadByCustomerId($oldBid->getCustomerId());
            if($oldBid->getAutobidId()){
                $oldBid->setData('over_autobid',$bidder->getOverAutobid());
                $this->_email->sendEmailOverAutoBid($oldBid,$newBid,$auction);
            }else {
                $oldBid->setData('over_bid',$bidder->getOverAutobid());
                $this->_email->sendEmailOverBid($oldBid, $newBid, $auction->getProductUrl());
            }
            $this->unblockMoneyOfUser($auction->getId(),$customerId);
        }
        $newBidder = $this->_bidderFactory->create()->loadByCustomerId($customerId);
        $newBid->setData('place_bid',$newBidder->getData('place_bid'));
        $newBid->setData('place_autobid',$newBidder->getData('place_autobid'));
        if($newBid->getAutobidId()){
            $this->_email->sendEmailNewAutoBidToBidder($newBid);
        }else {
            $this->_email->sendEmailNewBidToBidder($newBid);
        }
        $this->_email->sendEmailNewBidToAdmin($newBid);
        $this->_email->sendEmailNewBidToWatcher($newBid,$auction);
        return $this;
    }

    /**
     * @param $auctionId
     * @return \Magestore\Auction\Model\Bid|null
     */
    public function updateOverBid($auctionId){
        $enableBid = $this->getCollection()
            ->addFieldToFilter('auction_id', $auctionId)
            ->addFieldToFilter('status', self::BID_ENABLE)
            ->getFirstItem();
        if($enableBid->getId()){
            $enableBid->setStatus(self::BID_OVERED)->save();
            return $enableBid;
        }
        return null;
    }

    public function updateWinner($auction,$totalWinner,$reservedPrice){
        $auction_id = $auction->getId();
        $bids = $this->getCollection()
            ->joinBidderInfo()
            ->addFieldToFilter('auction_id',$auction_id)
            ->addFieldToFilter('price',['gteq' => $reservedPrice])
            ->addOrder('price',\Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $array = [];
        $i = 0;
        foreach($bids as $_bid){
            if(!in_array($_bid->getCustomerId(),$array)){
                if(!in_array($_bid->getStatus(),[self::BID_WINNER,self::BID_WON_AND_BOUGHT])) {
                    $_bid->setStatus(self::BID_WINNER)->save();
                    $this->_email->sendEmailToWinner($_bid);
                }
                $i++;
                $array[] = $_bid->getCustomerId();
                if($i >= $totalWinner) break;
            }
        }
        $lostBids = $this->getCollection()
            ->joinBidderInfo()
            ->addFieldToFilter('auction_id',$auction_id)
            ->addFieldToFilter('main_table.status',['nin' => [self::BID_WINNER,self::BID_LOSER]]);
        foreach($lostBids as $_bid){
            $_bid->setStatus(self::BID_LOSER)->save();
            if(!in_array($_bid->getCustomerId(),$array)) {
                $this->enabledMoney($auction_id, $_bid->getCustomerId(), 'Unlocked amount because no winner');
                $array[] = $_bid->getCustomerId();
                $this->_email->sendEmailFailderBid($auction, $_bid);
            }
        }
        return $i;
    }

    /**
     * @return string
     */
    public function getPriceText(){
        return $this->_checkoutHelper->formatPrice($this->getPrice());
    }

    /**
     * @return string
     */
    public function getFormatedPrice(){
        $formatPrice = $this->_checkoutHelper->formatPrice($this->getPrice());
        $formatPrice = str_replace('<span class="price">','',$formatPrice);
        $formatPrice = str_replace('</span>','',$formatPrice);
        return $formatPrice;
    }

    /**
     * @return \Magestore\Auction\Model\Bidder
     */
    public function getBidder(){
        if(!$this->getData('bidder')){
            $this->setData('bidder', $this->_bidderFactory->create()->loadByCustomerId($this->getCustomerId()));
        }
        return $this->getData('bidder');
    }

    /**
     * @return \Magestore\Auction\Model\Auction
     */
    public function getAuction(){
        if(!$this->getData('auction')){
            $this->setData('auction', $this->_auctionFactory->create()->load($this->getAuctionId()));
        }
        return $this->getData('auction');
    }


    /**
     * @param int $auctionId
     * @return \Magestore\Auction\Model\ResourceModel\Bid\Collection
     */
    public function getAuctionBids($auctionId){
        return $this->getCollection()->addFieldToFilter('auction_id',$auctionId);
    }

    /**
     * @param int $auctionId
     * @return int
     */
    public function getTotalBidder($auctionId){
        $collection = $this->getAuctionBids($auctionId);
//        $collection->getSelect()->group('customer_id');
        return $collection->getSize();
    }
    /**
     * @return string|null
     */
    public function getBidderName(){
        return $this->getBidder()->getBidderName();
    }

    /**
     * @return string|null
     */
    public function getProductName(){
        return $this->getAuctionName();
    }

    /**
     * @return string|null
     */
    public function getAuctionName(){
        return $this->getAuction()->getName();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(){
        if(!$this->getData('customer')){
            $this->setData('customer',$this->_customerFactory->create()->load($this->getCustomerId()));
        }
        return $this->getData('customer');
    }

    /**
     * @return mixed|string
     */
    public function getCustomerName(){
        return $this->getData('customer_name')?$this->getData('customer_name'):$this->getCustomer()->getName();
    }

    /**
     * @return mixed|string
     */
    public function getCustomerEmail(){
        return $this->getData('customer_name')?$this->getData('customer_email'):$this->getCustomer()->getEmail();
    }

    /**
     * @return string
     */
    public function getTimeLeft() {
        $timeleft = $this->getAuction()->getTimeLeft();
        if ($timeleft <= 0) {
            return __('0 days 0 hours 0 minutes');
        }
        $days = intval($timeleft / (3600 * 24));
        $time = $timeleft - $days * 3600 * 24;
        return __('%1 days %2 hours %3 minutes',$days,date('H',$time),date('i',$time));
    }
    /**
     * @return string
     */
    public function getFormatedTime(){
        return $this->_stdTimezone->formatDateTime($this->getCreatedTime());
    }
    /**
     * @return array
     */
    public function getEmailInfo(){
        return ['email' => $this->getCustomerEmail(),'name' => $this->getCustomerName()];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTypeLabel(){
        return $this->getAutobidId()?__('Auto bid'):__('Standrad bid');
    }
    /**
     * @return array
     */
    public static function getStatusArray(){
        return [
            self::BID_OVERED => __('Overed'),
            self::BID_ENABLE => __('Highest'),
            self::BID_WINNER => __('Win'),
            self::BID_LOSER => __('Lose'),
            self::BID_WON_AND_BOUGHT => __('Won and bought'),
            self::BID_CANCELED => __('Canceled')
        ];
    }
    /**
     * @return array
     */
    public static function getWinStatusArray(){
        return [
            self::BID_WINNER => __('Win'),
            self::BID_WON_AND_BOUGHT => __('Won and bought')
        ];
    }

    /**
     * @param $auction_id
     * @return string
     */
    public function getWinnerEmailList($auction_id){
        $winners = $this->getCollection()
            ->joinBidderInfo()
            ->addFieldToFilter('main_table.auction_id',$auction_id)
            ->addFieldToFilter('main_table.status', self::BID_WINNER);
        $emails = [];
        foreach($winners as $_winner){
            $emails[] = $_winner->getCustomerEmail();
        }
        return implode(',',$emails);
    }
    /**
     * @return string
     */
    public function getStatusLabel(){
        $array = self::getStatusArray();
            return $array[$this->getStatus()];
    }

    /**
     * @return bool
     */
    public function isAbleCancel(){
        return $this->getStatus() == self::BID_ENABLE;
    }

    /**
     * @return bool
     */
    public function isWinner(){
        return $this->getStatus() == self::BID_WINNER;
    }

    /**
     * @return bool
     */
    public function isBought(){
        return $this->getStatus() == self::BID_WON_AND_BOUGHT;
    }

    protected function enabledMoney($auction_id, $customer_id, $message)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $packagesCustomersFactory = $_objectManager->create('Magestore\Auction\Model\PackagesCustomers');
        return $packagesCustomersFactory->enabledMoney($auction_id,$customer_id, $message);
    }

    protected function unblockMoneyOfUser($auctionId,$userId)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $packagesCustomersFactory = $_objectManager->create('Magestore\Auction\Model\PackagesCustomers');
        $customers = $packagesCustomersFactory->getLosersUsers($auctionId,$userId);
        if(!empty($customers)) {
            foreach ($customers as $customer) {
                $this->enabledMoney($auctionId,$customer['id'],'Automatically unlock via higher bid');
            }
        }
    }

    public function checkBidderInAuction($auctionId,$customerId)
    {
        return $this->getCollection()
            ->addFieldToFilter('main_table.auction_id',$auctionId)
            ->addFieldToFilter('main_table.customer_id',$customerId);
    }
}