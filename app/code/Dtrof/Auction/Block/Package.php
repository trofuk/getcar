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
use Magestore\Auction\Model\PackagesCustomersFactory;
use Magestore\Auction\Model\PackagesFactory;

/**
 * Class Package
 * @package Magestore\Auction\Block
 */
class Package extends \Magestore\Auction\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Auction::package/title.phtml';

    /**
     * @var \Magestore\Auction\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var PackagesCustomersFactory
     */
    protected $_customersFactory;

    protected $_packagesFactory;

    /**
     * @var array
     */
    public $package = [];

    public function __construct(
        \Magestore\Auction\Block\Context $context,
        \Magestore\Auction\Helper\Data $helper,
        \Magestore\Auction\Model\PackagesCustomersFactory $customersFactory,
        \Magestore\Auction\Model\PackagesFactory $packagesFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->session = $customerSession;
        $this->_customersFactory = $customersFactory;
        $this->_packagesFactory = $packagesFactory;
    }

    public function showPackageName()
    {
        $customer = $this->_customersFactory->create()->load($this->session->getCustomerId());
        $package = $this->_packagesFactory->create()->load($customer->getPackageId($this->session->getCustomerId()));
        $this->package['info'] = $package;
        $blocked_balance = $customer->getCustomerActivitiesSum($this->session->getCustomerId());
        $this->package['blocked_balance'] = number_format($blocked_balance,2,'.',' ');
        $relationship = $customer->getRelationship($this->session->getCustomerId());
        if(!empty($relationship)) {
            $this->package['free_balance'] = number_format(($relationship['deposit']+$relationship['credit']-$blocked_balance), 2,'.',' ');
        } else {
            $this->package['free_balance'] =  number_format(0, 2,'.',' ');
        }
    }
}