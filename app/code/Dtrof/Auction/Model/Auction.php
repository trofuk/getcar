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
use Zend\Form\Element\DateTime;

/**
 * Auction Model
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Auction extends \Magento\Framework\Model\AbstractModel
{
    const AUCTION_STATUS_NOT_START = 0;
    const AUCTION_STATUS_PROCESSING = 1;
    const AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY = 2;
    const AUCTION_STATUS_CLOSED_WITHOUT_WINNER = 3;
    const AUCTION_STATUS_CLOSED = 4;
    const AUCTION_STATUS_DISABLED = 5;

    const ALLOW_BUY_OUT = 1;
    const NOT_ALLOW_BUY_OUT = 0;

    const IS_FEATURE = 1;
    const NOT_IS_FEATURE = 0;

    const SHOW_FOR_SLIDER = 1;
    const NOT_SHOW_FOR_SLIDER = 0;

    /**
     * @var BidFactory
     */
    protected $_bidFactory;

    /**
     * @var AutobidFactory
     */
    protected $_autobidFactory;


    /**
     * store view id.
     *
     * @var int
     */
    protected $_storeViewId = null;

    /**
     * auction factory.
     *
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;

    /**
     * value factory.
     *
     * @var \Magestore\Auction\Model\ValueFactory
     */
    protected $_valueFactory;

    /**
     * value collecion factory.
     *
     * @var \Magestore\Auction\Model\ResourceModel\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * [$_formFieldHtmlIdPrefix description].
     *
     * @var string
     */
    protected $_formFieldHtmlIdPrefix = 'page_';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * logger.
     *
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_monolog;
    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @var \Magestore\Auction\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;

    /**
     * @var \Magestore\Auction\Helper\Email
     */
    protected $_email;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProductHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_configurableProduct;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_db;

    /**
     * Auction constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Auction $resource
     * @param ResourceModel\Auction\Collection $resourceCollection
     * @param AuctionFactory $auctionFactory
     * @param ValueFactory $valueFactory
     * @param BidFactory $bidFactory
     * @param AutobidFactory $autobidFactory
     * @param ResourceModel\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Logger\Monolog $monolog
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
     * @param BidderFactory $bidderFactory
     * @param \Magestore\Auction\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Product $catalogProductHelper
     * @param \Magestore\Auction\Helper\Email $email
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProduct
     * @param \Magento\Framework\App\ResourceConnection $db
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Auction\Model\ResourceModel\Auction $resource,
        \Magestore\Auction\Model\ResourceModel\Auction\Collection $resourceCollection,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magestore\Auction\Model\ValueFactory $valueFactory,
        \Magestore\Auction\Model\BidFactory $bidFactory,
        \Magestore\Auction\Model\AutobidFactory $autobidFactory,
        \Magestore\Auction\Model\ResourceModel\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Logger\Monolog $monolog,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magestore\Auction\Model\BidderFactory $bidderFactory,
        \Magestore\Auction\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magestore\Auction\Helper\Email $email,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProduct,
        \Magento\Framework\App\ResourceConnection $db
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_bidderFactory = $bidderFactory;
        $this->_registry = $registry;
        $this->_auctionFactory = $auctionFactory;
        $this->_valueFactory = $valueFactory;
        $this->_valueCollectionFactory = $valueCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_bidFactory = $bidFactory;
        $this->_autobidFactory = $autobidFactory;
        $this->_stdTimezone = $_stdTimezone;
        $this->_monolog = $monolog;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_helper = $helper;
        $this->_email = $email;
        $this->_catalogProductHelper = $catalogProductHelper;
        $this->_configurableProduct = $configurableProduct;
        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
        $this->_db = $db->getConnection();
    }

    /**
     * get form field html id prefix.
     *
     * @return string
     */
    public function getFormFieldHtmlIdPrefix()
    {
        return $this->_formFieldHtmlIdPrefix;
    }


    /**
     * get store attributes.
     *
     * @return array
     */
    public static function getStoreAttributes()
    {
        return array(
            'name',
            'status',
        );
    }

    /**
     * get store view id.
     *
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * set store view id.
     *
     * @param int $storeViewId
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

    /**
     * before save.
     */
    public function beforeSave()
    {
        if ($this->getStoreViewId()) {
            $defaultStore = $this->_auctionFactory->create()->setStoreViewId(null)->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            $data = $this->getData();
            foreach ($storeAttributes as $attribute) {
                if (isset($data['use_default']) && isset($data['use_default'][$attribute])) {
                    $this->setData($attribute.'_in_store', false);
                } else {
                    $this->setData($attribute.'_in_store', true);
                    $this->setData($attribute.'_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultStore->getData($attribute));
            }
        }
        if($this->getData('multi_winner')<1){
            $this->setData('multi_winner',1);
        }
        return parent::beforeSave();
    }

    /**
     * after save.
     */
    public function afterSave()
    {
        if ($storeViewId = $this->getStoreViewId()) {
            $storeAttributes = $this->getStoreAttributes();

            foreach ($storeAttributes as $attribute) {
                $attributeValue = $this->_valueFactory->create()
                    ->loadAttributeValue($this->getId(), $storeViewId, $attribute);
                if ($this->getData($attribute.'_in_store')) {
                    try {
                        if ($attribute == 'image' && $this->getData('delete_image')) {
                            $attributeValue->delete();
                        } else {
                            $attributeValue->setValue($this->getData($attribute.'_value'))->save();
                        }
                    } catch (\Exception $e) {
                        $this->_monolog->addError($e->getMessage());
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (\Exception $e) {
                        $this->_monolog->addError($e->getMessage());
                    }
                }
            }
        }

        return parent::afterSave();
    }

    /**
     * load info multistore.
     *
     * @param mixed  $id
     * @param string $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        if ($this->getStoreViewId()) {
            $this->getStoreViewValue();
        }

        return $this;
    }

    /**
     * get store view value.
     *
     * @param string|null $storeViewId
     *
     * @return $this
     */
    public function getStoreViewValue($storeViewId = null)
    {
        if (!$storeViewId) {
            $storeViewId = $this->getStoreViewId();
        }
        if (!$storeViewId) {
            return $this;
        }
        $storeValues = $this->_valueCollectionFactory->create()
            ->addFieldToFilter('auction_id', $this->getId())
            ->addFieldToFilter('store_id', $storeViewId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode().'_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }
        return $this;
    }

    /**
     * @return \Magestore\Auction\Model\Bid
     */
    public function getLastBid(){
        if(!$this->getData('last_bid')) {
            $this->setData('last_bid',$this->_bidFactory->create()->getLastBid($this->getId()));
        }
        return $this->getData('last_bid');
    }

    /**
     * @return \Magestore\Auction\Model\Bid
     */
    public function getLastAutoBid(){
        if(!$this->getData('last_autobid')) {
            $this->setData('last_autobid',$this->_autobidFactory->create()->getLastBid($this->getId()));
        }
        return $this->getData('last_autobid');
    }

    /**
     * @return float
     */
    public function getCurrentBidderName(){
        return ($this->getLastBid()&&$this->getLastBid()->getId()) ? $this->getLastBid()->getBidderName():'';
    }

    /**
     * @return float
     */
    public function getCustomerIdOfLastBid(){
        return ($this->getLastBid()->getId()) ? $this->getLastBid()->getCustomerId():null;
    }

    /**
     * @return float
     */
    public function getCurrentPrice(){
//        if($this->getPrice()){
//            return $this->getPrice();
//        }
        return ($this->getLastBid()->getId()) ? $this->getLastBid()->getPrice():(float)$this->getInitPrice();
    }

    /**
     * @return float
     */
    public function getMinNextPrice(){
        return ($this->getLastBid()->getId()) ? $this->getMinIntervalPrice() + $this->getCurrentPrice(): $this->getCurrentPrice();
    }
    /**
     * @return float|null
     */
    public function getMaxNextPrice(){
        return $this->getMaxIntervalPrice()>$this->getMinIntervalPrice() ? $this->getMaxIntervalPrice() + $this->getCurrentPrice() : null;
    }

    /**
     * @return string
     */
    public function getMinNextPriceText(){
        return $this->_checkoutHelper->formatPrice($this->getMinNextPrice());
    }
    /**
     * @return string
     */
    public function getMaxNextPriceText(){
        return $this->_checkoutHelper->formatPrice($this->getMaxNextPrice());
    }

    /**
     * add by Oleg
     */
    public function getMinIncrement(){
        return $this->getMinIntervalPrice();
    }
    /**
     * @return string
     */

    public function getIncrementPrice()
    {
        return $this->_checkoutHelper->formatPrice($this->getMinIntervalPrice());
    }
    /**
     * @return string
     */
    public function getCurrentPriceText()
    {
        return $this->_checkoutHelper->formatPrice($this->getCurrentPrice());
    }
    /**
     * @return string
     */
    public function getStartPrice()
    {
        return $this->_checkoutHelper->formatPrice($this->getInitPrice());
    }
    /**
     * @return string
     */
    public function getStartPriceEmailText()
    {
        $formatPrice = $this->_checkoutHelper->formatPrice($this->getInitPrice());
        $formatPrice = str_replace('<span class="price">','',$formatPrice);
        $formatPrice = str_replace('</span>','',$formatPrice);
        return $formatPrice;
    }
    /**
     * @return string
     */
    public function getCurrentPriceEmailText()
    {
        $formatPrice = $this->_checkoutHelper->formatPrice($this->getCurrentPrice());
        $formatPrice = str_replace('<span class="price">','',$formatPrice);
        $formatPrice = str_replace('</span>','',$formatPrice);
        return $formatPrice;
    }

    /**
     * @return string
     */
    public function getLocaleEndTime()
    {
        return $this->_stdTimezone->formatDateTime($this->getEndTime());
    }
    /**
     * @return string
     */
    public function getLocaleStartTime()
    {
        return $this->_stdTimezone->formatDateTime($this->getStartTime());
    }

    /**
     * @param $customerId
     * @return bool
     */
    public function isInWatchList($customerId){
        return in_array($customerId,explode(',',$this->getWatchList()));
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function addToWatchList($customerId){
        if($this->isInWatchList($customerId))
            return $this;
        $array = explode(',',$this->getWatchList());
        $array[] = $customerId;
        $array = array_filter(array_unique($array));
        $this->setWatchList(implode(',',$array));
        try{
            $this->save();
        }catch(\Exception $e){

        }
        return $this;
    }

    /**
     * @param $customerId
     * @return $this|void
     */
    public function removeFromWatchList($customerId){
        if(!$this->isInWatchList($customerId))
            return;
        $array = explode(',',$this->getWatchList());
        foreach($array as $key => $_value){
            if($_value == $customerId){
                unset($array[$key]);
            }
        }
        $this->setWatchList(implode(',',$array));
        try{
            $this->save();
        }catch(\Exception $e){

        }
        return $this;
    }

    /**
     * @return bool|\Magestore\Auction\Model\Auction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCurrentAuction(){
        $product = $this->_registry->registry('current_product');
        if($product==null||!$product->getId()) {
            return false;
        }
        if($this->isWinner($product->getId())){
            $customer_id = $this->_bidderFactory->create()->getCurrentCustomer()->getId();
            return $this->getCollection()->getWinAuction($customer_id,$product->getId());
        }
        $auction = $this->getResourceCollection()
            ->addFieldToFilter('product_id',$product->getId())
            ->addFieldToFilter('status',['in'=>[
                self::AUCTION_STATUS_PROCESSING,
                self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY
            ]])
            ->setOrder('status')
            ->getLastItem();
        if($auction->getId()){
            $dateTimeNow = date('Y-m-d H:i:s');
            if($auction->getStartTime()< $dateTimeNow && $auction->getEndTime() <= $dateTimeNow){
                $auction->updateStatus()->setStoreViewId(0)->save();
            }
            if(!in_array($auction->getStatus(),[self::AUCTION_STATUS_PROCESSING,self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY])){
                return false;
            }
            return $auction;
        }
        return false;
    }

    /**
     * @return bool|\Magestore\Auction\Model\Auction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByProductId($product_id){

        $auction = $this->getResourceCollection()
            ->addFieldToFilter('product_id',$product_id)
            ->addFieldToFilter('status',['in'=>[
                self::AUCTION_STATUS_NOT_START,
                self::AUCTION_STATUS_PROCESSING
            ]])
            ->setOrder('status')
            ->getLastItem();

        if($this->isWinner($product_id, $auction->getId())){
            return $this->getCollection()->getWinAuctionInfo($product_id);
        }

        if($auction->getId()){
            $dateTimeNow = date('Y-m-d H:i:s');
            if(($auction->getStartTime()< $dateTimeNow && $auction->getStatus()==self::AUCTION_STATUS_NOT_START) ||
                ($auction->getStatus()==self::AUCTION_STATUS_PROCESSING && $auction->getStartTime()< $dateTimeNow && $auction->getEndTime() <= $dateTimeNow)){
                    $auction->setIsChangedStatus(true);
                    $auction->updateStatus()->setStoreViewId(0)->save();
            }
            if(!in_array($auction->getStatus(),[self::AUCTION_STATUS_PROCESSING,self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY])){
                return false;
            }
            return $auction;
        }
        return false;
    }

    /**
     * @param $bidder
     * @param $price
     * @return bool
     */
    public function createNewAutobid($bidder,$price){
        $hightestAutobid = $this->_autobidFactory->create()->getOverAutobid($this);
        $autobid = $this->_autobidFactory->create()->createNewAutoBid($this->getId(), $bidder->getCustomerId(), $price);
        if(!$hightestAutobid->getId()){
            $lastBid = $this->getLastBid();
            if(!$lastBid || $lastBid->getCustomerId()!=$autobid->getCustomerId()){
                $bid = $this->_bidFactory->create()->createNewBid($this, $autobid->getCustomerId(), $this->getMinNextPrice(), $autobid->getId());
            }
        } elseif($hightestAutobid->getCustomerId() != $autobid->getCustomerId()) {
            if($hightestAutobid->getPrice()<$autobid->getPrice()){
                $bid = $this->_bidFactory->create()->createNewBid($this, $hightestAutobid->getCustomerId(), $hightestAutobid->getPrice(), $hightestAutobid->getId());
                $this->setData('last_bid',$bid);
                $price = min([$this->getMinNextPrice(),$autobid->getPrice()]);
                $bid = $this->_bidFactory->create()->createNewBid($this, $autobid->getCustomerId(), $price, $autobid->getId());
            }elseif($hightestAutobid->getPrice()==$autobid->getPrice()){
                $price = max([$this->getCurrentPrice(),$price-$this->getData('min_interval_price')]);
                $this->_bidFactory->create()->createNewBid($this, $autobid->getCustomerId(), $price, $autobid->getId());
                $bid = $this->_bidFactory->create()->createNewBid($this, $hightestAutobid->getCustomerId(), $autobid->getPrice(), $hightestAutobid->getId());
            }else{
                $bid = $this->_bidFactory->create()->createNewBid($this, $autobid->getCustomerId(), $autobid->getPrice(), $autobid->getId());
                $this->setData('last_bid',$bid);
                $price = min([$this->getMinNextPrice(),$hightestAutobid->getPrice()]);
                $bid = $this->_bidFactory->create()->createNewBid($this, $hightestAutobid->getCustomerId(), $price, $hightestAutobid->getId());
            }
        } elseif($hightestAutobid->getCustomerId() == $autobid->getCustomerId()){
            $lastBid = $this->getLastBid();
            if(!$lastBid || $lastBid->getCustomerId()!=$autobid->getCustomerId()){
                $bid = $this->_bidFactory->create()->createNewBid($this, $autobid->getCustomerId(), $this->getMinNextPrice(), $autobid->getId());
            }
        }
        if(isset($bid)) {
            $this->setData('last_bid', $bid);
            $this->updateTimeLeft();
            $this->updateBids();
        }
        return $autobid;
    }

    /**
     * @return float
     */
    public function getLastAutobidPrice(){
        return $this->getLastAutoBid()?$this->getLastAutoBid()->getPrice():0;
    }
    /**
     * @return string
     */
    public function getLastAutobidPriceText(){
        return $this->_checkoutHelper->formatPrice($this->getLastAutobidPrice());
    }


    /**
     * @param $bidId
     * @param $customerId
     * @return bool
     */

    public function cancelBid($bidId,$customerId){
        $bid = $this->_bidFactory->create()->getCollection()
            ->joinBidderInfo()
            ->addFieldToFilter('main_table.status',\Magestore\Auction\Model\Bid::BID_ENABLE)
            ->addFieldToFilter('main_table.bid_id',$bidId)
            ->addFieldToFilter('main_table.customer_id',$customerId)
            ->getFirstItem();
        if($bid->getId()) {
            $bid->setStatus(\Magestore\Auction\Model\Bid::BID_CANCELED)->save();
            if($bid->getAutobidId()){
                $autobid = $this->_autobidFactory->create()->load($bid->getAutobidId());
                if($autobid->getId()){
                    $autobid->setStatus(\Magestore\Auction\Model\Autobid::AUTOBID_DISABLE)
                    ->save();
                }
            }
            $this->_email->sendEmailCancelBid($bid);
            $preBid = $this->_bidFactory->create()->getCollection()
                ->joinBidderInfo()
                ->addFieldToFilter('main_table.auction_id',$bid->getAuctionId())
                ->addFieldToFilter('main_table.status',\Magestore\Auction\Model\Bid::BID_OVERED)
                ->addOrder('main_table.price', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
                ->getFirstItem();
            if($preBid->getId()){
                $preBid->setStatus(\Magestore\Auction\Model\Bid::BID_ENABLE)
                    ->save();
                $this->_email->sendEmailHighest($preBid);
            }
            return true;
        }
        return false;
    }
    /**
     * @param $bidder
     * @param $price
     * @return bool
     */
    public function createNewBid($bidder,$price){
        $bid = $this->_bidFactory->create()->createNewBid($this, $bidder->getCustomerId(), $price);
        $this->setData('last_bid',$bid);
        if($this->_helper->isEndableAutobid()){
            $lastBid = $this->_afterCreateNewBid();
            if($lastBid!=null){
                $this->setData('last_bid',$lastBid);
            }
        }
        $this->updateTimeLeft();
        $this->updateBids();
        return $bid;
    }

    /**
     * @return bool
     */
    public function updateTimeLeft(){
        if($this->getTimeLeft() <= $this->getData('limit_time')){
            $currentTimestamp = strtotime(date('Y-m-d H:i:s')) + (int)$this->getData('limit_time');
            $endtime = date('Y-m-d H:i:s',$currentTimestamp);
            $this->setEndTime($endtime)->setStoreViewId(0)->save();
            return true;
        }
        return false;
    }

    /**
     * @retun \Magestore\Aution\Model\Bid|null
     */
    protected function _afterCreateNewBid(){
        if($this->getLastBid()->getPrice()<$this->getLastAutobidPrice()){
            $price = min([$this->getMinNextPrice(),$this->getLastAutobidPrice()]);
            $bid = $this->_bidFactory->create()->createNewBid($this, $this->getLastAutoBid()->getCustomerId(), $price, $this->getLastAutoBid()->getId());
            if($bid->getId()){
                return $bid;
            }
        }
        return null;
    }

    public function getProductUrl(){
        return $this->_catalogProductHelper->getProductUrl($this->getProductId());
    }
    /**
     * @return array
     */
    public function getProcessingAuctionsInfo(){
        $auctions = $this->getProcessingAuctions();
        $array = [];
        foreach($auctions as $_auction){
            $array[$_auction->getProductId()]['end_time'] = $_auction->getTimeLeft();
            $array[$_auction->getProductId()]['price'] = $_auction->getCurrentPriceText();
            $array[$_auction->getProductId()]['bidder_name'] = $_auction->getBidderName();
        }
        return $array;
    }

    /**
     * @return array
     */
    public function getFeatureAuctionsInfo(){
        $auctions = $this->getProcessingAuctions()
            ->addFieldToFilter('featured',self::IS_FEATURE);
        $array = [];
        foreach($auctions as $_auction){
            $array[$_auction->getProductId()]['end_time'] = $_auction->getTimeLeft();
            $array[$_auction->getProductId()]['price'] = $_auction->getCurrentPriceText();
            $array[$_auction->getProductId()]['bidder_name'] = $_auction->getBidderName();
        }
        return $array;
    }

    /**
     * @return int
     */
    public function getTimeLeft(){
        $timeLeft = strtotime($this->getEndTime())-$this->getNow();
        return $timeLeft>0 ? $timeLeft : 0;
    }

    public function getTimeToStart()
    {
        $timeToStart = strtotime($this->getStartTime())-$this->getNow();
        return $timeToStart>0 ? $timeToStart : 0;
    }

    public function getNow()
    {
        $now = $this->_stdTimezone->date()->setTimezone(new \DateTimeZone('UTC'))->format("Y-m-d H:i:s");
        $time = strtotime($now);
        return $time;
    }

    /**
     * @return \Magestore\Auction\Model\ResourceModel\Auction\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getProcessingAuctions()
    {
        return $this->getCollection()
            ->joinTheBidderNameAndPrice()
            ->addFieldToFilter('main_table.status',self::AUCTION_STATUS_PROCESSING)
            ->addFieldToSelect(['end_time','product_id','init_price']);
    }


    /**
     * @return \Magestore\Auction\Model\ResourceModel\Bid\Collection
     */
    public function updateBids(){
        $this->setData('bids', $this->_bidFactory->create()->getCollection()->addFieldToFilter('auction_id', $this->getId())->addOrder('created_time'));
        return $this->getData('bids');
    }
    /**
     * @return \Magestore\Auction\Model\ResourceModel\Bid\Collection
     */
    public function getBids(){
        if(!$this->getData('bids')) {
            $this->setData('bids', $this->_bidFactory->create()->getCollection()->addFieldToFilter('auction_id', $this->getId())->addOrder('price')->addOrder('created_time'));
        }
        return $this->getData('bids');
    }
    /**
     * @return int
     */
    public function getTotalBids(){
        return $this->getBids()->addFieldToFilter('status',['nin'=>Bid::BID_CANCELED])->getSize();
    }

    /**
     * @return int
     */
    public function getTotalBider(){
        return $this->_bidFactory->create()->getCollection()->addFieldToFilter('auction_id', $this->getId())->getTotalBidder($this->getId());
    }

    /**
     * @param $lastBid
     */
    public function sendOverBidEmail($lastBid)
    {
        return;
    }

    /**
     * @return int
     */
    public function setWinner(){
        return $this->_bidFactory->create()->updateWinner($this,$this->getData('multi_winner'),$this->getData('reserved_price'));
    }

    /**
     * @return \Magento\Framework\Phrase|null|string
     */
    public function getWinnerName(){
        $lastbid = $this->getLastBid();
        if($lastbid->getId()){
            return $lastbid->getBidderName();
        }else return __('none');
    }

    public function getWinnerEmailList(){
        return $this->_bidFactory->create()->getWinnerEmailList($this->getId());
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isWinner($productId = null, $auctionId = null){
        $productId = $productId ? $productId : $this->getProductId();
        $bidder = $this->_bidderFactory->create()->getCurrentBidder()->getWonInfo($productId,$auctionId);
        return $bidder->getId()? true:false;
    }

    /**
     * @param $productId
     * @var \Magestore\Auction\Model\Auction $auction
     * @return bool
     */
    public function checkIsSaleAle($productId){
        if(!$this->_bidderFactory->create()->isLoggedIn()){
            $collection1 = $this->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('status',self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY)
                ->addFieldToFilter('allow_buyout',self::NOT_ALLOW_BUY_OUT);
            $collection1->getSelect()
                ->where('ADDDATE(main_table.end_time,INTERVAL main_table.day_to_buy DAY) >= now()');

            $collection2 = $this->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('status',self::AUCTION_STATUS_PROCESSING)
                ->addFieldToFilter('allow_buyout',self::NOT_ALLOW_BUY_OUT);
            return !($collection2->getSize()||$collection1->getsize());
        }
        $customer = $this->_bidderFactory->create()->getCurrentCustomer();
        $bids = $this->_bidFactory->create()->getCollection()
            ->addFieldToFilter('main_table.status', Bid::BID_WINNER);
        $bids->getSelect()->joinLeft(
            ['auction' => $bids->getTable('magestore_auction')],
            'main_table.auction_id = auction.auction_id',
            ['product_id' => 'auction.product_id']
        );
        $bids->addFieldToFilter('auction.product_id',$productId)
            ->addFieldToFilter('customer_id',$customer->getId());
        if($bids->getSize()){
            return true;
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status',self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY)
            ->addFieldToFilter('allow_buyout',self::NOT_ALLOW_BUY_OUT);
        $collection->getSelect()
            ->where('ADDDATE(main_table.end_time,INTERVAL main_table.day_to_buy DAY) >= now()');
        if($collection->getSize()){
            return false;
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status',self::AUCTION_STATUS_PROCESSING)
            ->addFieldToFilter('allow_buyout',self::NOT_ALLOW_BUY_OUT);
        if($collection->getSize()){
            return false;
        }
        return true;
    }



    public function isSaleAble(){
        if ($this->getAllowBuyout() == self::ALLOW_BUY_OUT) {
            if ($this->getStatus() == self::AUCTION_STATUS_PROCESSING) {
                return false;
            } elseif ($this->getStatus() == self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY) {
                $timeStap = strtotime(date('Y-m-d H:i:s'));
                $dayToBuy = strtotime($this->getEndTime()) + $this->getDayToBuy() * 86400;
                if ($dayToBuy < $timeStap || $this->isWinner()) {
                    return true;
                }
                return false;
            }
        }
    }

    /**
     * @return int
     */
    public function hasWinnerWaitToBuy(){
        return $this->_bidFactory->create()
            ->getCollection()
            ->addFieldToFilter('auction_id', $this->getId())
            ->addFieldToFilter('status',\Magestore\Auction\Model\Bid::BID_WINNER)
            ->getSize();
    }


    /*
     * update auctions status
     */
    public function updateStatus(){
        if(in_array($this->getStatus(),[
                self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY,
                self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER,
                self::AUCTION_STATUS_CLOSED,
                self::AUCTION_STATUS_DISABLED
            ]
        )){
            return $this;
        }
        $dateTimeNow  = date('Y-m-d H:i:s');
        if($this->getStartTime() > $dateTimeNow){
            $this->setStatus(self::AUCTION_STATUS_NOT_START);
        }elseif($this->getStartTime()<= $dateTimeNow && $this->getEndTime() > $dateTimeNow){
            $this->setStatus(self::AUCTION_STATUS_PROCESSING);
        }elseif($this->getStartTime()< $dateTimeNow && $this->getEndTime() <= $dateTimeNow){
            if(!$this->getId()||$this->setWinner()==0){
                $this->setStatus(self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER);
            }else{
                $this->setStatus(self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY);
            }
            if(!$this->hasWinnerWaitToBuy()){
                $this->setStatus(self::AUCTION_STATUS_CLOSED);
            }
            $this->_email->sendEmailAuctionCompletedToAdmin($this);
            $this->_email->sendEmailAuctionCompletedToWatcher($this);
        }
        return $this;
    }


    /**
     * @return $this
     */
    public function updateStatusMultiAuctions(){
        $auctions = $this->getCollection()
            ->addFieldToFilter('end_time',['lteq'=>date('Y-m-d H:i:s')])
            ->addFieldToFilter('status',['in'=>[self::AUCTION_STATUS_PROCESSING,self::AUCTION_STATUS_NOT_START]]);
        foreach($auctions as $_auction){
            $_auction->updateStatus()->setStoreViewId(0)->save();
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getProductAuctionIds(){
        return $this->getCollection()
            ->addFieldToFilter('status',['in'=>[self::AUCTION_STATUS_PROCESSING,self::AUCTION_STATUS_NOT_START]])
            ->getAllField('product_id');
    }

    /**
     * get available statuses.
     *
     * @return boolean
     */
    public function isReadOnly()
    {
        return in_array($this->getStatus(),[
            self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY,
            self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER,
            self::AUCTION_STATUS_CLOSED,
            self::AUCTION_STATUS_DISABLED
        ]);
    }
    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getAvailableStatuses()
    {
        return [
            self::AUCTION_STATUS_NOT_START                          => __('Not Start'),
            self::AUCTION_STATUS_PROCESSING                         => __('Processing'),
            self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY   => __('Finish and wait to buy'),
            self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER              => __('Closed without winner'),
            self::AUCTION_STATUS_CLOSED                             => __('Closed'),
            self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
        ];
    }

    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getAvailableMassStatuseAction()
    {
        return [
            ['value' => self::AUCTION_STATUS_NOT_START, 'label' => __('Enable')],
            ['value' => self::AUCTION_STATUS_DISABLED, 'label' => __('Disable')],
        ];
    }


    /**
     * @return string
     */
    public function getStatusLabel(){
        switch($this->getStatus()){
            case self::AUCTION_STATUS_NOT_START:
                return __('Not Start');
            case self::AUCTION_STATUS_PROCESSING:
                return __('Processing');
            case self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY:
                return __('Finish and wait to buy');
            case self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER:
                return __('Closed without winner');
            case self::AUCTION_STATUS_CLOSED:
                return __('Closed');
            case self::AUCTION_STATUS_DISABLED:
                return __('Disabled');
        }
    }

    /**
     * get available statuses.
     *
     * @return []
     */
    public function getEditAbleStatuses()
    {
        switch($this->getStatus()){
            case self::AUCTION_STATUS_NOT_START:
                return [
                    self::AUCTION_STATUS_NOT_START                          => __('Enable'),
                    self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
                ];
            case self::AUCTION_STATUS_PROCESSING:
                return [
                    self::AUCTION_STATUS_PROCESSING                         => __('Processing'),
                    self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
                ];
            case self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY:
                return [
                    self::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY   => __('Finish and wait to buy'),
                    self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
                ];
            case self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER:
                return [
                    self::AUCTION_STATUS_CLOSED_WITHOUT_WINNER              => __('Closed without winner'),
                    self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
                ];
            case self::AUCTION_STATUS_CLOSED:
                return [
                    self::AUCTION_STATUS_CLOSED                             => __('Closed'),
                    self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
                ];
            case self::AUCTION_STATUS_DISABLED:
                return [
                    self::AUCTION_STATUS_CLOSED                             => __('Enable'),
                    self::AUCTION_STATUS_DISABLED                           => __('Disabled'),
                ];
        };
    }

    /**
     * @return bool
     */
    public function isInProcessing(){
        return $this->getStatus() == self::AUCTION_STATUS_PROCESSING;
    }

    public function getPackagesForOption()
    {
        $array = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $packageCollection = $objectManager->create('Magestore\Auction\Model\Packages');
        $package = $packageCollection->getCollection();
        $data = $package->getData();
        foreach ($data as $item) {
            $array[$item['package_id']] = $item['name'];
        }
        return $array;
    }

    public function setPackage($auction_id, $package_id)
    {
        $data = $this->_db->fetchRow("SELECT `id` FROM `magestore_auction_package_relationship`
        WHERE `auction_id` = ".$auction_id." LIMIT 1");
        if($data['id'] == 0){
            $this->_db->insert('magestore_auction_package_relationship',[
                'package_id' => $package_id,
                'auction_id' => $auction_id
            ]);
        } else {
            $this->_db->update('magestore_auction_package_relationship',array('package_id'=>$package_id,'auction_id' => $auction_id),'id='.$data['id']);
        }
    }

    public function getPackageId($auction_id)
    {
        $data = $this->_db->fetchRow("SELECT `package_id` FROM `magestore_auction_package_relationship`
        WHERE `auction_id` = ".$auction_id." LIMIT 1");
        if($data['package_id'] > 0) {
            return $data['package_id'];
        } else {
            return 0;
        }
    }

    public function getBidStatus()
    {
        $customer = $this->_bidderFactory->create();
        if($customer->getCurrentCustomerId() > 0) {
            if($this->getTotalBider() > 0) {
                if($this->checkBidderInAuction($this->getId(), $customer->getCurrentCustomerId())){
                    $lastBidCustomerId = $this->getCustomerIdOfLastBid();
                    if($lastBidCustomerId != null && $lastBidCustomerId != $customer->getCurrentCustomerId()) {
                        return __('Your bid has been over. Please try again');
                    }
                }
            }
        }
        return false;
    }

    public function checkBidderInAuction($auction_id, $customer_id)
    {
        $bidder = $this->_bidFactory->create();
        $bid = $bidder->checkBidderInAuction($auction_id,$customer_id);
        $data = $bid->getData();
        if(!empty($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkTakePartInAuction()
    {
        $customer = $this->_bidderFactory->create();
        if($customer->getCurrentCustomerId() > 0) {
            if($this->checkBidderInAuction($this->getId(), $customer->getCurrentCustomerId())){
                return true;
            }
        }
        return false;
    }

}