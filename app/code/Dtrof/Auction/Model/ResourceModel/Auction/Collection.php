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

namespace Magestore\Auction\Model\ResourceModel\Auction;
use \Magestore\Auction\Model\Auction;
use \Magestore\Auction\Model\Bid;

/**
 * Auction Collection
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Auction\Model\Auction', 'Magestore\Auction\Model\ResourceModel\Auction');
    }

    /**
     * @return $this
     */
    public function joinTheBidderNameAndPrice(){
        $this->getSelect()
            ->joinLeft(['bid' => $this->getTable('magestore_auction_bid')],
                'main_table.auction_id = bid.auction_id and bid.status =' . \Magestore\Auction\Model\Bid::BID_WINNER,
                ['price' => 'bid.price','customer_id' => 'bid.customer_id'])
            ->joinLeft(['bidder' => $this->getTable('magestore_auction_bidder')],
                'bid.customer_id = bidder.customer_id',
                ['bidder_name' => 'bidder.bidder_name']);
        return $this;
    }

    public function getWinAuctions($customerId){
        $this->getSelect()
            ->joinLeft(['bid' => $this->getTable('magestore_auction_bid')],
                'main_table.auction_id = bid.auction_id',
                ['customer_id' => 'bid.customer_id','price' => 'bid.price','bid_id' => 'bid.bid_id']);
        $this->addFieldToFilter('bid.customer_id' , $customerId)
            ->addFieldToFilter('bid.status', \Magestore\Auction\Model\Bid::BID_WINNER);
        return $this;
    }

    public function getWinAuction($customerId,$productId,$auctionId = 0){
        $this->getSelect()
            ->joinLeft(['bid' => $this->getTable('magestore_auction_bid')],
                'main_table.auction_id = bid.auction_id',
                ['customer_id' => 'bid.customer_id','price' => 'bid.price','bid_id' => 'bid.bid_id']);
        if($auctionId > 0) {
            $this->addFieldToFilter('main_table.auction_id',$auctionId);
        }
        return $this->addFieldToFilter('bid.customer_id' , $customerId)
            ->addFieldToFilter('bid.status', \Magestore\Auction\Model\Bid::BID_WINNER)
            ->addFieldToFilter('main_table.product_id',$productId)
            ->getFirstItem();
    }

    public function getWinAuctionInfo($productId)
    {
        $this->getSelect()->limit(1)->order('end_time DESC')
            ->join(['bid' => $this->getTable('magestore_auction_bid')],
                'main_table.auction_id = bid.auction_id',
                ['customer_id' => 'bid.customer_id','price' => 'bid.price','bid_id' => 'bid.bid_id']);
        return $this->addFieldToFilter('main_table.product_id',$productId)->getFirstItem();
    }


    public function getAllField($name){
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($name, 'main_table');
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    public function addCustomerInfo($customerId,$joinBidderInfo = true){
        $this->getSelect()
            ->joinLeft(['bid' => $this->getTable('magestore_auction_bid')],
                'main_table.auction_id = bid.auction_id',
                ['customer_id' => 'bid.customer_id','price' => 'bid.price','bid_id' => 'bid.bid_id']);
        $this->addFieldToFilter('bid.customer_id' , $customerId);
        if($joinBidderInfo){
            $this->getSelect()->joinLeft(['bidder' => $this->getTable('magestore_auction_bidder')],
                'bid.customer_id = bidder.customer_id');
            $this->getSelect()->joinLeft(['customer' => $this->getTable('customer_entity')],
                'bid.customer_id = customer.entity_id',
                ['customer_email' => 'customer.email','customer_name' => 'customer.firstname']);
        }
        return $this;
    }

    public function checkAllowBuyOut($productId,$customerId){
        $this->addFieldToFilter('main_table.product_id',$productId);
        $this->addFieldToFilter('main_table.allow_buyout',Auction::NOT_ALLOW_BUY_OUT);
        $this->getSelect()->joinLeft(['bid' => $this->getTable('magestore_auction_bid')],
            'main_table.auction_id = bid.auction_id and MAX(bid.price)>1',
            ['customer_id' => 'bid.customer_id']);
        $this->getSelect()
            ->where('(main_table.status = '.Auction::AUCTION_STATUS_PROCESSING.') or (ADDDATE(main_table.end_time,INTERVAL main_table.day_to_buy DAY) >= now() and main_table.status = '.Auction::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY.')');
        return $this;
    }

    public function addProductAuctionInfo($productId = null,$customerId=null,$joinBidderInfo = false, $checkAllowBuyOut = false ){
        if($productId){
            $this->addFieldToFilter('product_id',$productId);
        }
        if($customerId){
            $this->getSelect()
                ->joinLeft(['bid' => $this->getTable('magestore_auction_bid')],
                    'main_table.auction_id = bid.auction_id',
                    ['customer_id' => 'bid.customer_id','price' => 'bid.price','bid_id' => 'bid.bid_id']);
            $this->addFieldToFilter('bid.customer_id' , $customerId);
            if($joinBidderInfo){
                $this->getSelect()->joinLeft(['bidder' => $this->getTable('magestore_auction_bidder')],
                    'bid.customer_id = bidder.customer_id');
                $this->getSelect()->joinLeft(['customer' => $this->getTable('customer_entity')],
                    'bid.customer_id = customer.entity_id',
                    ['customer_email' => 'customer.email','customer_name' => 'customer.firstname']);
            }
        }
        if($checkAllowBuyOut){
            $this->getSelect()
                ->where('ADDDATE(end_time,INTERVAL main_table.day_to_buy DAY) >= now()');
            $this->addFieldToFilter('allow_buyout',Auction::NOT_ALLOW_BUY_OUT);
        }
        return $this;
    }

    public function getAuctionInitPrice($productId = null)
    {
        if($productId){
            $this->addFieldToFilter('product_id',$productId);
        }
        return $this;
    }

    public function getAuctionForHomePageSlider()
    {
        $nin = [
            Auction::AUCTION_STATUS_FINISHED_AND_WAIT_FOR_WINNER_BUY,
            Auction::AUCTION_STATUS_CLOSED_WITHOUT_WINNER,
            Auction::AUCTION_STATUS_CLOSED,
            Auction::AUCTION_STATUS_DISABLED,
        ];
        $this->getSelect()
            ->where('`end_time` > \''.date('Y-m-d H:i:s').'\'')
            ->where('`status` NOT IN (\''.implode('\',\'',$nin).'\')');
        $this->addFieldToFilter('show_for_slider', Auction::SHOW_FOR_SLIDER);
        return $this;
    }
}