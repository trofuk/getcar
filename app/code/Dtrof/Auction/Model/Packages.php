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
class Packages extends \Magento\Framework\Model\AbstractModel
{
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
        \Magestore\Auction\Model\ResourceModel\Packages $resource,
        \Magestore\Auction\Model\ResourceModel\Packages\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );

        $this->_collection = $this->getCollection();
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

    public function getAllPackages()
    {
        return $this->_collection->getData();
    }

    public function getPackages($packageId)
    {
        $this->_collection->addFieldToFilter('main_table.package_id', $packageId);
        $data = $this->_collection->getFirstItem()->getData();
        return $data;
    }


}