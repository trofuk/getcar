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

namespace Magestore\Auction\Model\ResourceModel\Autobid;

/**
 * Autobid Collection
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
        $this->_init('Magestore\Auction\Model\Autobid', 'Magestore\Auction\Model\ResourceModel\Autobid');
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
}