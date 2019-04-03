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

namespace Magestore\Auction\Block;
use Magento\Customer\Model\Session;

/**
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Auction extends \Magestore\Auction\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Auction::auctionbox.phtml';
    /**
     * @var \Magestore\Auction\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;


    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_config;

    /**
     * @var \Magestore\Auction\Model\PackagesCustomers
     */
    protected $_packagesCustomers;

    /**
     * @var \Magestore\Auction\Model\PackagesAuctions
     */
    protected $_packagesAuctions;

    /**
     * @var \Magestore\Auction\Model\Packages
     */
    protected $_packages;
    /**
     * View constructor.
     *
     * @param Context $context
     * @param \Magestore\Auction\Helper\Data $helper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magestore\Auction\Block\Context $context,
        \Magestore\Auction\Helper\Data $helper,
        \Magestore\Auction\Model\BidderFactory $bidFactory,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magestore\Auction\Model\PackagesCustomers $packagesCustomers,
        \Magestore\Auction\Model\PackagesAuctions $packagesAuctions,
        \Magestore\Auction\Model\Packages $packages,
        Session $customerSession,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->_bidderFactory = $bidFactory;
        $this->_auctionFactory = $auctionFactory;
        $this->_helper = $helper;
        parent::__construct($context, $data);
        $this->_config = $context->getSystemConfig();
        $this->_isScopePrivate = false;
        $this->_packagesCustomers = $packagesCustomers;
        $this->_packagesAuctions = $packagesAuctions;
        $this->_packages = $packages;
    }

    /**
     * @return \Magestore\Auction\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    public function getLoadedProductCollection()
    {
        return $this->_objectManager->get('Magento\Catalog\Block\Product\ListProduct')->getLoadedProductCollection();
    }


    /**
     * @return \Magestore\Auction\Model\Auction
     */
    public function getCurrentAuction(){
        if (!$this->hasData('current_auction')) {
            $auction = $this->_auctionFactory->create()->loadCurrentAuction();
            $this->setData('current_auction', $auction);
        }
        return $this->getData('current_auction');
    }



    /**
     * @return \Magestore\Auction\Model\Bidder
     */
    public function getCurrentBidder(){
        if (!$this->hasData('current_bidder')) {
            $auction = $this->_bidderFactory->create()->getCurrentBidder();
            $this->setData('current_bidder', $auction);
        }
        return $this->getData('current_bidder');
    }

    /**
     * @return mixed
     */
    public function getCurrentAuctionId(){
        return $this->getCurrentAuction()->getId();
    }

    /**
     * @return string
     */
    public function getViewBidUrl(){
        return $this->getUrl('auction/index/viewbids',array('auction_id'=>$this->getAuction()->getId()));
    }
    /**
     * @return bool
     */
    public function getAuctionLink()
    {
        $auction = $this->getAuction();
        if ($auction->getAuctionUrl()) {
            return $auction->getAuctionUrl();
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isLogedIn(){
        $productId = $this->_request->getParam('product_id');
        return $this->getCurrentBidder()->isLoggedIn($productId);
    }
    /**
     * @return bool
     */
    public function hasBidName(){
        return !$this->getCurrentBidder()->getBidderName()=='';
    }

    /**
     * @return bool
     */
    public function getCurrentCustomer(){
        return $this->session->getCustomer();
    }
    /**
     * @return bool
     */
    public function getCurrentCustomerId(){
        return $this->session->getCustomerId();
    }

    /**
     * @return bool
     */
    public function enableBid(){
        return $this->isLogedIn() && $this->hasBidName();
    }

    public function getPostBidderNameActionUrl(){
        return $this->getUrl('auction/index/postBidderName');
    }

    public function isWinner($auctionId = 0){

        $auction = $this->getCurrentBidder()->getWonInfo($this->getAuction()->getProductId(),$auctionId);
        return $auction->getId();
    }

    public function getWonMessage()
    {
        return $this->_config->wonMessage();
    }

    public function getUpdateTime(){
        return $this->_config->updateTime();
    }

    public function showPrice(){
        return $this->_config->showPrice();
    }
    public function autoBid(){
        return $this->_config->autobid();
    }

    /**
     * @param \Magestore\Auction\Model\Auction $auction
     * @return string
     */
    public function getSuggess($auction){
        if($auction->getTotalBids()){
            return $auction->getMaxNextPrice()?__('Your bid must be greater than %1 and less than %2',$auction->getMinNextPriceText(),$auction->getMaxNextPriceText()):__('Your bid must be greater than %1',$auction->getMinNextPriceText());
        }
        return $auction->getMaxNextPrice()?__('Your bid must be equal or greater than %1 and less than %2',$auction->getMinNextPriceText(),$auction->getMaxNextPriceText()):__('Your bid must be equal or greater than %1',$auction->getMinNextPriceText());;
    }

    public function alertMessage($auction)
    {
        if($this->getCurrentCustomerId() > 0) {
            if($this->checkUserGroupAccess() == 1) {
                $userId = $this->getCurrentCustomerId();
                $userInfo = $this->_packagesCustomers->getCustomer($userId);
                if (isset($userInfo['package_id']) && $userInfo['package_id'] > 0) {
                    $auctionInfo = $this->_packagesAuctions->getAuction($auction->getId());
                    $userPackagesInfo = $this->_packages->getPackages($userInfo['package_id']);
                    $auctionPackagesInfo = $this->_packages->getPackages($auctionInfo['package_id']);
                    $initPrice = $auction->getInitPrice();
                    if ($initPrice <= $userPackagesInfo['upper_price']) {
                        $balance = $userInfo['deposit'] + $userInfo['credit'];
                        $activitiesSum = $this->_packagesCustomers->getCustomerActivitiesSum($userId);
                        $currentBalance = $balance - $activitiesSum;
                        if ($auctionPackagesInfo['amount_blocked'] > $currentBalance) {
                            $amount_blocked = \Magento\Framework\App\ObjectManager::getInstance()->create('Magestore\Auction\Helper\Auction')->getFormatCurrency($auctionPackagesInfo['amount_blocked']);
                            return __('You have insufficient funds in the account to participate in this auction.<br>To participate you need %1', $amount_blocked);
                        }
                    } else {
                        return __('Your package not allows you to participate in this auction.');
                    }
                } else {
                    return __('You are not allowed to participate in the auction. Contact your support');
                }
            } else {
                return __('Your account is not entitled to participate in the auction. Contact your support');
            }
        } else {
            return __('Please <a href="%1" >login</a> to bid',$this->getUrl('auction/index/redirectToLogin',['product_id' => $auction->getProductId()]));
        }
    }

    public function checkUserSession()
    {
        if($this->session->isLoggedIn()){
            return 1;
        } else {
            return 0;
        }
    }

    public function checkUserGroupAccess()
    {
        if($this->checkUserSession()) {
            $blocked_groups_id = $this->getData('blocked_groups_id');
            $array = array();
            foreach (explode(',',$blocked_groups_id) as $item) {
                $item = trim($item);
                if($item != '' && is_numeric($item)) {
                    $array[] = $item;
                }
            }
            if(!in_array($this->session->getCustomer()->getGroupId(),$array)) {
                return 1;
            }
        }
        return 0;
    }

}