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
 * Packages Model
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Dtrof Developer
 */
class PackagesAuctions extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection;

    /**
     * Packages constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Packages $resource
     * @param ResourceModel\Packages\Collection $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Auction\Model\ResourceModel\PackagesAuctions $resource,
        \Magestore\Auction\Model\ResourceModel\PackagesAuctions\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_collection = $this->getCollection();
    }

    public function getAuction($auctionId)
    {
        $this->_collection->addFieldToFilter('main_table.auction_id', $auctionId);
        $data = $this->_collection->getFirstItem()->getData();
        return $data;
    }

    public function getAuctionByProductId($productId = 0)
    {
        if($productId > 0) {
            $connection = $this->_collection->getConnection();
            $themeTable = $connection->getTableName('magestore_auction');
//            $sql = "SELECT * FROM ".$themeTable." WHERE `product_id` = ".$productId." AND `status` IN (0,1,2)";
            $sql = "SELECT * FROM ".$themeTable." WHERE `product_id` = ".$productId." ORDER BY `auction_id` DESC";
            $data = $connection->fetchRow($sql);
            $auction = $this->getAuction($data['auction_id']);
            if(!empty($auction)) {
                $data['package_id'] = $auction['package_id'];
            }
            return $data;
        }
    }

}