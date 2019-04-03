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
class PackagesCustomers extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection;
    protected $_connection;

/**
    protected $_packagesCustomersFactory;
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
        \Magestore\Auction\Model\ResourceModel\PackagesCustomers $resource,
        \Magestore\Auction\Model\ResourceModel\PackagesCustomers\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_collection = $this->getCollection();
        $this->_connection = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ResourceConnection');
    }

    public function getPackageId($userId)
    {
        $this->_collection->addFieldToFilter('main_table.entity_id', $userId);
        $data = $this->_collection->getFirstItem()->getData();
        if(isset($data['package_id']) && $data['package_id'] > 0) {
            return $data['package_id'];
        } else {
            return 0;
        }
    }

    public function customerRelationship($userId,$packageId)
    {
        $connection = $this->_connection->getConnection();
        $themeTable = $this->_connection->getTableName('magestore_auction_package_customers');
        $itemId = $this->checkRelationship($userId);
        if($itemId > 0) {
            $sql = "UPDATE " . $themeTable . " SET `package_id` = ".$packageId." WHERE `id` = ".$itemId." LIMIT 1";
        } else {
            $sql = "INSERT INTO " . $themeTable . " (`package_id`, `entity_id`) VALUES (".$packageId.", ".$userId.")";
        }
        $connection->query($sql);
    }

    public function updateCustomerBalance($data)
    {
        $connection = $this->_connection->getConnection();
        $themeTable = $this->_connection->getTableName('magestore_auction_package_customers');
        $itemId = $this->checkRelationship($data['entity_id']);
        if($itemId > 0) {
            $sql = "UPDATE " . $themeTable . " SET `deposit` = ".$data['deposit'].", `credit` = ".$data['credit'].", `package_id` = ".$data['package_id']." WHERE `id` = ".$itemId." LIMIT 1";
        } else {
            $sql = "INSERT INTO " . $themeTable . " (`package_id`, `entity_id`, `deposit`, `credit`) VALUES (".$data['package_id'].", ".$data['entity_id'].", ".$data['deposit'].", ".$data['credit'].")";
        }
        if($connection->query($sql)) {
            $themeTable = $this->_connection->getTableName('magestore_auction_customer_history');
            $sql = "INSERT INTO " . $themeTable . " (`entity_id`, `admin_id`, `action_time`, `action`, `data`) VALUES (".$data['entity_id'].", ".$data['admin_id'].", '".date('Y-m-d H:i:s')."', 'Update balance', '".json_encode($data)."')";
            return $connection->query($sql);
        } else {
            return 0;
        }

    }

    public function checkRelationship($userId)
    {
        $this->_collection->addFieldToFilter('main_table.entity_id', $userId);
        $data = $this->_collection->getFirstItem()->getData();
        if(isset($data['id']) && $data['id'] > 0) {
            return $data['id'];
        } else {
            return 0;
        }
    }

    public function getRelationship($userId)
    {
        $this->_collection->addFieldToFilter('main_table.entity_id', $userId);
        $data = $this->_collection->getFirstItem()->getData();
        return $data;
    }

    public function getCustomer($userId)
    {
        $this->_collection->addFieldToFilter('main_table.entity_id', $userId);
        $data = $this->_collection->getFirstItem()->getData();
        return $data;
    }

    public function getCustomerActivitiesSum($userId, $auctionId = 0)
    {
        $sql_str = "";
        if($auctionId > 0) {
            $sql_str = " AND `auction_id` != ".$auctionId;
        }
        $connection = $this->_connection->getConnection();
        $themeTable = $this->_connection->getTableName('magestore_auction_customer_activites');
        $sql = "SELECT SUM(`blocked_sum`) AS `total` FROM ".$themeTable." WHERE `entity_id` = ".$userId.$sql_str;
        $query = $connection->fetchRow($sql);
        return $query['total'];
    }

    public function setCustomerActivities($auctionId, $userId)
    {
        if($this->checkCustomerActivities($auctionId, $userId) == 0){
            $userInfo = $this->getCustomer($userId);
            $connection = $this->_connection->getConnection();
            $themeTable = $this->_connection->getTableName('magestore_auction_package');
            $sql = "SELECT `amount_blocked` FROM ".$themeTable." WHERE `package_id` = ".$userInfo['package_id']." LIMIT 1";
            $data = $connection->fetchRow($sql);
            $themeTable = $this->_connection->getTableName('magestore_auction_customer_activites');
            $sql = "INSERT INTO " . $themeTable . " (`entity_id`, `auction_id`, `blocked_sum`, `activites_date`)
            VALUES
            (".$userId.", ".$auctionId.", ".$data['amount_blocked'].", '".date('Y-m-d H:i:s')."')";
            return $connection->query($sql);
        }
    }

    public function checkCustomerActivities($auctionId, $userId)
    {
        $connection = $this->_connection->getConnection();
        $themeTable = $this->_connection->getTableName('magestore_auction_customer_activites');
        $sql = "SELECT `id` FROM ".$themeTable." WHERE `entity_id` = ".$userId." AND `auction_id` = ".$auctionId." LIMIT 1";
        $data = $connection->fetchRow($sql);
        if(isset($data['id']) && $data['id'] > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function enabledMoney($auctionId,$userId,$message='No name action')
    {
        $connection = $this->_connection->getConnection();
        $themeTable = $this->_connection->getTableName('magestore_auction_customer_activites');
        $sql = "SELECT `id` FROM ".$themeTable." WHERE `entity_id` = ".$userId." AND `auction_id` = ".$auctionId." LIMIT 1";
        $data = $connection->fetchRow($sql);
        if(isset($data['id']) && $data['id'] > 0) {
            $sql = "DELETE FROM ".$themeTable." WHERE `entity_id` = ".$userId." AND `auction_id` = ".$auctionId." LIMIT 1";
            if($connection->query($sql)) {
                $themeTable = $this->_connection->getTableName('magestore_auction_customer_history');
                $sql = "INSERT INTO " . $themeTable . " (`entity_id`, `action_time`, `action`, `data`) VALUES (".$userId.", '".date('Y-m-d H:i:s')."', '".$message."', '".json_encode($data)."')";
                return $connection->query($sql);
            }
        }
    }

    public function getLosersUsers($auctionId,$userId)
    {
        $connection = $this->_connection->getConnection();
        $themeTable = $this->_connection->getTableName('magestore_auction_customer_activites');
        $sql = "SELECT `entity_id` AS `id` FROM ".$themeTable." WHERE `entity_id` != ".$userId." AND `auction_id` = ".$auctionId." LIMIT 1";
        return $connection->fetchAll($sql);
    }
}