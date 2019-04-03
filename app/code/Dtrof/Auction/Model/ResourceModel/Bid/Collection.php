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

namespace Magestore\Auction\Model\ResourceModel\Bid;
use Magestore\Auction\Model\Bid;
/**
 * Bid Collection
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
        $this->_init('Magestore\Auction\Model\Bid', 'Magestore\Auction\Model\ResourceModel\Bid');
    }

    public function getTotalBidder(){
        $this->addFieldToFilter('status',['nin'=>Bid::BID_CANCELED]);
        $this->getSelect()->group('customer_id');
        return $this->getSize();
    }

    /**
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Select_Exception
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);

        // Count doesn't work with group by columns keep the group by
        if(count($this->getSelect()->getPart(\Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(\Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(\Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    /**
     * @return $this
     */
    public function joinBidderInfo(){
        $this->getSelect()->joinLeft(['bidder' => $this->getTable('magestore_auction_bidder')],
            'main_table.customer_id = bidder.customer_id');
        $this->getSelect()->joinLeft(['customer' => $this->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['customer_email' => 'customer.email','customer_name' => 'customer.firstname']);
        return $this;
    }
    /**
     * @return $this
     */
    public function joinBidderName(){
        $this->getSelect()->joinLeft(['bidder' => $this->getTable('magestore_auction_bidder')],
            'main_table.customer_id = bidder.customer_id',
            ['bidder_name' => 'bidder.bidder_name']);
        return $this;
    }
    /**
     * @return $this
     */
    public function getTransaction(){
        $this
            ->addFieldToFilter('main_table.status',\Magestore\Auction\Model\Bid::BID_WON_AND_BOUGHT)
            ->getSelect()->joinLeft(['auction' => $this->getTable('magestore_auction')],
            'main_table.auction_id = auction.auction_id',
            ['name' => 'auction.name']);
        $this->getSelect()
            ->join($this->getTable('sales_order'), $this->getTable('sales_order').'.entity_id = main_table.order_id', array("order_number" => "increment_id", "created_at", "total_amount" => "grand_total"))
        ;
        return $this;
    }

    public function joinCustomer(){
        $this->getSelect()->joinLeft(['customer' => $this->getTable('customer_entity')],
            'bidder.customer_id = customer.entity_id',
            [
                'customer_email' => 'customer.email',
                'customer_name' => 'CONCAT(customer.firstname," ",customer.lastname)'
            ]);
        return $this;
    }
}