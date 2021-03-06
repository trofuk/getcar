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

namespace Magestore\Auction\Model\ResourceModel\Activities;
use Magestore\Auction\Model\Activities;
/**
 * Packages Collection
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
        $this->_init('Magestore\Auction\Model\Activities', 'Magestore\Auction\Model\ResourceModel\Activities');
    }

    /**
     * @return $this
     */
    public function getActivities(){
        $this->getSelect();
        return $this;
    }
}