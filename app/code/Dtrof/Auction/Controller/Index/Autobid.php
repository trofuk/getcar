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

use Magestore\Auction\Model\Auction;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class Autobid extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;

    /**
     * @var \Magestore\Auction\Model\PackagesCustomers
     */
    protected $_packagesCustomers;

    /**
     * @var mixed
     */
    protected $_packagesAuctions;

    /**
     * @var mixed
     */
    protected $_packages;

    /**
     * AutoBid constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Auction\Model\Auction $auctionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magestore\Auction\Model\BidderFactory $bidderFactory
    ) {
        parent::__construct($context);
        $this->_auctionFactory = $auctionFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_bidderFactory = $bidderFactory;
        $this->_packagesCustomers = $this->_objectManager->create('Magestore\Auction\Model\PackagesCustomers');
        $this->_packagesAuctions = $this->_objectManager->create('Magestore\Auction\Model\PackagesAuctions');
        $this->_packages = $this->_objectManager->create('Magestore\Auction\Model\Packages');

    }

    /**
     * Execute action
     * @var \Magestore\Auction\Model\Auction $auction
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        /*
         * check is loged in
         */
        if (!$this->_bidderFactory->create()->isLoggedIn($this->getRequest()->getParam('product_id'))) {
            if($this->getCurrentCustomerId() > 0) {
                $message = ['error' => $this->alertMessage()];
            } else {
                $message = __('Please login to bid!');
            }
            $result->setData(['error'=>$message]);
            return $result;
        }
        /*
         * check customer created autoBid name
         */
        $bidder = $this->_bidderFactory->create()->getCurrentBidder();
        if (!$bidder->getBidderName()) {
            $result->setData(['error'=>__('Please enter your bidder name before autoBid!')]);
            return $result;
        }

        /*
         * check the auction exist
         */
        $auction = $this->_auctionFactory->create()->load($this->getRequest()->getParam('auction_id'));
        if(!$auction->getId()){
            $result->setData(['error'=>__('The auction is not exist!')]);
            return $result;
        }

        /*
         * check the auction is in proccess
         * @var \Magestore\Auction\Model\Auction $auction
         */
        $auction->updateStatus();
        if($auction->getStatus()!=Auction::AUCTION_STATUS_PROCESSING){
            $result->setData(['error'=>__('The auction is not in proccess!')]);
            return $result;
        }
        $price = $this->getRequest()->getParam('price');

        /*
         * check the price is number
         */
        if(!is_numeric($price)){
            $result->setData(['error' => __('The price is not numberic')]);
            return $result;
        }

        /*
         * check the price is number
         */
        if($price>=100000000||$price<=0){
            $result->setData(['error' => __('The price is invalid.')]);
            return $result;
        }

        /*
         * check the price is larger Min next price
         */
        if($price < $auction->getMinNextPrice()){
            $result->setData(['error' => __('The autobid need greater than %1', $auction->getMinNextPriceText())]);
            return $result;
        }

        /*
         * create aution bid
         */
        $autobid = $auction->createNewAutobid($bidder,$price);

        $this->_packagesCustomers->setCustomerActivities(
            $auction->getId(),
            $this->getCurrentCustomerId()
        );
        /*
         * check the bid created successfully
         */
        if($autobid->getCustomerId()!=$auction->getLastBid()->getCustomerId()){
            $result->setData(['error' => __('You took a autobid with %1 plus. However, another customer had already taken an auto-bid which is greater than your bid.',$autobid->getPriceText()),
                'current_price' => $auction->getCurrentPriceText(),
                'min_next_price' => $auction->getMinNextPrice(),
                'current_bidder_name' => $auction->getCurrentBidderName(),
                'total_bid' => __('%1 bids', $auction->getTotalBids()),
                'time_left' => $auction->getTimeLeft()
            ]);
            return $result;
        }

        /*
         * bid successfully
         */
        $result->setData(['success' => __('You created an autobid with price is %1', $auction->getLastAutobidPriceText()),
                          'current_price' => $auction->getCurrentPriceText(),
                          'current_bidder_name' => $auction->getCurrentBidderName(),
                          'total_bid' => __('%1 bids', $auction->getTotalBids()),
                          'time_left' => $auction->getTimeLeft()
                    ]);
        return $result;
    }

    public function alertMessage()
    {
        $auction = $this->_auctionFactory->create()->load($this->getRequest()->getParam('auction_id'));
        if($this->getCurrentCustomerId() > 0) {
            if($this->checkUserGroupAccess() == 1) {
                $userId = $this->getCurrentCustomerId();
                $userInfo = $this->_packagesCustomers->getCustomer($userId);
                $auctionInfo = $this->_packagesAuctions->getAuction($auction->getId());
                $userPackagesInfo = $this->_packages->getPackages($userInfo['package_id']);
                $auctionPackagesInfo = $this->_packages->getPackages($auctionInfo['package_id']);
                $initPrice = $auction->getInitPrice();
                if($initPrice <= $userPackagesInfo['upper_price']) {
                    $balance = $userInfo['deposit'] + $userInfo['credit'];
                    $activitiesSum = $this->_packagesCustomers->getCustomerActivitiesSum($userId);
                    $currentBalance = $balance - $activitiesSum;
                    if($auctionPackagesInfo['amount_blocked'] > $currentBalance) {
                        $amount_blocked = \Magento\Framework\App\ObjectManager::getInstance()->create('Magestore\Auction\Helper\Auction')->getFormatCurrency($auctionPackagesInfo['amount_blocked']);
                        return __('You have insufficient funds in the account to participate in this auction.<br>To participate you need %1',$amount_blocked);
                    }
                } else {
                    return __('Your package not allows you to participate in this auction.');
                }
            } else {
                return __('Your account is not entitled to participate in the auction. Contact your support');
            }
        } else {
            return __('Please <a href="%1" >login</a> to bid',$this->getUrl('auction/index/redirectToLogin',['product_id' => $auction->getProductId()]));
        }
    }


    public function getCurrentCustomerId()
    {
        return $this->_bidderFactory->create()->getCurrentCustomerId();
    }

    public function getCurrentCustomerGroupId()
    {
        return $this->_bidderFactory->create()->getCurrentCustomerGroupId();
    }

    public function checkUserGroupAccess()
    {
        return $this->_bidderFactory->create()->checkCurrentUserGroupAccess();
    }
}