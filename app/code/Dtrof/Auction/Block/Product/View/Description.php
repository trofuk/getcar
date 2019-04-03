<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product description block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magestore\Auction\Block\Product\View;

use Magento\Catalog\Model\Product;

class Description extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magestore\Auction\Model\PackagesCustomers
     */
    protected $_packagesCustomers;

    /**
     * @var \Magestore\Auction\Model\Packages
     */
    protected $_packages;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $session,
        \Magestore\Auction\Model\PackagesCustomers $packagesCustomers,
        \Magestore\Auction\Model\Packages $packages,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->session = $session;
        $this->_packagesCustomers = $packagesCustomers;
        $this->_packages = $packages;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(){
        if($this->checkUserSession()){
            return $this->checkUserAuctionAccess();
        } else {
            return 0;
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

    public function checkUserAuctionAccess()
    {
        if($this->checkUserGroupAccess() == 1){
            $packageId = $this->_packagesCustomers->getPackageId($this->session->getCustomer()->getId());
            $collection = $this->_packages->getCollection();
            $collection->addFieldToFilter('main_table.package_id', $packageId);
            $data = $collection->getFirstItem()->getData();
            if(isset($data['package_id']) && $data['package_id'] > 0) {
                return 1;
            }
        }
        return 0;
    }
}
