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
use \Magestore\Auction\Model\Source\Biddernametype;

/**
 * Autobid Model
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Bidder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ResourceModel\Auction\CollectionFactory
     */
    protected $_autionCollectionFactory;
    /**
     * @var ResourceModel\Bid\CollectionFactory
     */
    protected $_bidCollectionFactory;

    /**
     * @var ResourceModel\Autobid\CollectionFactory
     */
    protected $_autobidCollectionFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var PackagesFactory
     */
    protected $_packagesFactory;

    /**
     * @var PackagesCustomersFactory
     */
    protected $_packagesCustomersFactory;

    /**
     * @var PackagesCustomers
     */
    protected $_packagesCustomers;

    /**
     * @var Packages
     */
    protected $_packages;

    /**
     * @var PackagesAuctions
     */
    protected $_packagesAuctions;

    /**
     * @var AuctionFactory
     */
    protected $_auctionFactory;

    /**
     * Bidder constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Bidder $resource
     * @param ResourceModel\Bidder\Collection $resourceCollection
     * @param ResourceModel\Auction\CollectionFactory $auctionCollectionFactory
     * @param ResourceModel\Bid\CollectionFactory $bidCollectionFactory
     * @param ResourceModel\Autobid\CollectionFactory $autobidCollectionFactory
     * @param SystemConfig $config
     * @param \Magento\Customer\Model\Session $seccion
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Auction\Model\ResourceModel\Bidder $resource,
        \Magestore\Auction\Model\ResourceModel\Bidder\Collection $resourceCollection,
        \Magestore\Auction\Model\ResourceModel\Auction\CollectionFactory $auctionCollectionFactory,
        \Magestore\Auction\Model\ResourceModel\Bid\CollectionFactory $bidCollectionFactory,
        \Magestore\Auction\Model\ResourceModel\Autobid\CollectionFactory $autobidCollectionFactory,
        \Magestore\Auction\Model\PackagesFactory $packagesFactory,
        \Magestore\Auction\Model\PackagesCustomersFactory $packagesCustomersFactory,
        \Magestore\Auction\Model\PackagesCustomers $packagesCustomers,
        \Magestore\Auction\Model\Packages $packages,
        \Magestore\Auction\Model\PackagesAuctions $packagesAuctions,
        \Magestore\Auction\Model\SystemConfig $config,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\Session $seccion
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_autionCollectionFactory = $auctionCollectionFactory;
        $this->_bidCollectionFactory = $bidCollectionFactory;
        $this->_autobidCollectionFactory = $autobidCollectionFactory;
        $this->session = $seccion;
        $this->_config = $config;
        $this->_request = $request;
        $this->_packagesFactory = $packagesFactory;
        $this->_packagesCustomersFactory = $packagesCustomersFactory;
        $this->_packagesCustomers = $packagesCustomers;
        $this->_packages = $packages;
        $this->_packagesAuctions = $packagesAuctions;
    }

    /**
     * @return $this
     */
    public function getCurrentBidder(){
        return $this->loadByCustomerId($this->session->getCustomerId());
    }

    /**
     * @param int $customerId
     * @return \Magestore\Auction\Model\bidder
     */
    public function loadByCustomerId($customerId){
        return $this->getCollection()
            ->addFieldToFilter('customer_id',$customerId)
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();
    }

    /**
     * @return \Magestore\Auction\Model\ResourceModel\Bid\Collection
     */
    public function getBidsCollection(){
        $customerId = $this->getCustomerId()?$this->getCustomerId():$this->session->getCustomerId();
        return $this->_bidCollectionFactory->create()
            ->addFieldToFilter('customer_id',$customerId)
            ->addOrder('created_time',\Magento\Framework\Data\Collection::SORT_ORDER_DESC);
    }

    /**
     * @return \Magestore\Auction\Model\ResourceModel\Bid\Collection
     */
    public function getAutobidsCollection(){
        $customerId = $this->getCustomerId()?$this->getCustomerId():$this->session->getCustomerId();
        return $this->_autobidCollectionFactory->create()
            ->addFieldToFilter('customer_id',$customerId)
            ->addOrder('created_time',\Magento\Framework\Data\Collection::SORT_ORDER_DESC);
    }

    /**
     * @return \Magestore\Auction\Model\ResourceModel\Auction\Collection
     */
    public function getWatchListAuctions(){
        $customerId = $this->getCustomerId()?$this->getCustomerId():$this->session->getCustomerId();
        return $this->_autionCollectionFactory->create()
            ->addFieldToFilter('watch_list',['finset'=>$customerId])
            ->addOrder('end_time',\Magento\Framework\Data\Collection::SORT_ORDER_DESC);
    }

    /**
     * @param array $data
     * @return $this|bool
     */
    public function UpdateForCurrentBidder(array $data){
        $data['customer_id'] = $this->session->getCustomerId();
        $data['bidder_id'] = $this->getCurrentBidder()->getId();
        $array = ['place_bid','place_autobid','over_bid','over_autobid','cancel_bid','highest_bid'];
        foreach($array as $value){
            if(!isset($data[$value])){
                $data[$value] = '0';
            }
        }
        $bidder = $this->getCurrentBidder()->setData($data);
        try{
            $bidder->save();
        }catch(\Exception $e){}
        if($bidder->getId())
            return $bidder;
        return false;
    }

    /**
     * @return bool
     */
    public function isLoggedIn($productId = null){
        if($this->session->isLoggedIn()) {
            return $this->checkPlaceBid($productId);
        } else {
            return 0;
        }
    }
    /**
     * @return bool
     */
    public function isEnabled(){
        return ($this->session->isLoggedIn());
    }

    /**
     * @return array
     */
    public function getWonAuctionsInfo(){
        $collection = $this->_autionCollectionFactory->create()->getWinAuctions($this->getCustomerId());
        $array = [];
        foreach($collection as $_auction){
            $array[$_auction->getProductId()]['price'] = $_auction->getPrice();
            $array[$_auction->getProductId()]['product_id'] = $_auction->getProductId();
            $array[$_auction->getProductId()]['bid_id'] = $_auction->getBidId();
        }
        return $array;
    }

    /**
     * @param $productId
     * @return \Magestore\Auction\Model\Auction
     */
    public function getWonInfo($productId,$auctionId = 0){
        $auction = $this->_autionCollectionFactory->create()->getWinAuction($this->getCustomerId(),$productId,$auctionId);
        return $auction;
    }

    /**
     * @return mixed|string
     */
    public function getBidderName(){
        if($this->getData('bidder_name'))
            return $this->getData('bidder_name');
        switch($this->_config->bidderNameType()) {
            case Biddernametype::CREATE_BY_CUSTOMER:
                return '';
            case Biddernametype::BASE_ON_CUSTOMERS_NAME:
                $customer = $this->session->getCustomer();
                $bidder = $this->getCurrentBidder()
                    ->setCustomerId($customer->getId())
                    ->setData('bidder_name', $customer->getFirstName().' '.$customer->getLastname());
                if($customer->getId())
                    $bidder->save();
                return $bidder->getData('bidder_name');
            case Biddernametype::GENNERATE_BY_SYSTEM:
                $preFix = $this->_config->bidderNamePrifix()?$this->_config->bidderNamePrifix():__('Bidder');
                $customer = $this->session->getCustomer();
                $bidder = $this->getCurrentBidder()
                    ->setCustomerId($customer->getId())
                    ->setData('bidder_name', $preFix . ' '. rand(1000,9999));
                if($customer->getId())
                    $bidder->save();
                return $bidder->getData('bidder_name');
        }
        return '';
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer(){
        return $this->session->getCustomer();
    }

    public function getCurrentCustomerId()
    {
        return $this->session->getCustomerId();
    }

    public function getCurrentCustomerGroupId()
    {
        return $this->session->getCustomerGroupId();
    }

    protected function checkPlaceBid($productId = null)
    {
        if($productId == null) {
            $productId = $this->_request->getParam('product_id');
        }
        if($this->getCurrentCustomerId() > 0) {
            if($this->checkCurrentUserGroupAccess() == 1) {
                $userId = $this->getCurrentCustomerId();
                $userInfo = $this->_packagesCustomers->getCustomer($userId);
                if (isset($userInfo['package_id']) && $userInfo['package_id'] > 0) {
                    $auctionInfo = $this->_packagesAuctions->getAuctionByProductId($productId);
                    $userPackagesInfo = $this->_packages->getPackages($userInfo['package_id']);
                    $auctionPackagesInfo = $this->_packages->getPackages($auctionInfo['package_id']);
                    $initPrice = $auctionInfo['init_price'];
                    if ($initPrice <= $userPackagesInfo['upper_price']) {
                        $balance = $userInfo['deposit'] + $userInfo['credit'];
                        $activitiesSum = $this->_packagesCustomers->getCustomerActivitiesSum($userId, $auctionInfo['auction_id']);
                        $currentBalance = $balance - $activitiesSum;
                        if ($auctionPackagesInfo['amount_blocked'] <= $currentBalance) {
                            return 1;
                        }
                    }
                }
            }
        }
        return 0;
    }

    public function checkCurrentUserGroupAccess()
    {
        $blocked_groups_id = $this->getData('blocked_groups_id');
        $array = array();
        foreach (explode(',',$blocked_groups_id) as $item) {
            $item = trim($item);
            if($item != '' && is_numeric($item)) {
                $array[] = $item;
            }
        }
        if(!in_array($this->getCurrentCustomerGroupId(),$array)) {
            return 1;
        }
        return 0;
    }
}