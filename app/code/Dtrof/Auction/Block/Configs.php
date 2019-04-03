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
use Magento\Customer\Model\Session;

/**
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Configs extends \Magestore\Auction\Block\AbstractBlock
{
    /**
     * @var \Magestore\Auction\Model\Bidder
     */
    protected $_bidder;

    /**
     * Configs constructor.
     * @param Context $context
     * @param \Magestore\Auction\Model\Bidder $bidder
     * @param array $data
     */
    public function __construct(
        \Magestore\Auction\Block\Context $context,
        \Magestore\Auction\Model\Bidder $bidder,
        array $data = []
    ) {
        $this->_bidder = $bidder;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Auction Settings'));
    }

    /**
     * @return \Magestore\Auction\Model\Bidder
     */
    public function getCurrentBidder(){
        if(!$this->getData('current_bidder')){
            $this->setData('current_bidder',$this->_bidder->getCurrentBidder());
        }
        return $this->getData('current_bidder');
    }

    /**
     * @return string
     */
    public function getPostConfigsUrl()
    {
        return $this->getUrl('auction/index/postauctionconfigs',['configpage'=>1]);
    }
}