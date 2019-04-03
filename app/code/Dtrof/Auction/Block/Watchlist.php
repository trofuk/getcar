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
class Watchlist extends \Magestore\Auction\Block\AbstractBlock
{
    /**
     * @var \Magestore\Auction\Model\Bidder
     */
    protected $_bidder;

    /**
     * Mybids constructor.
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
        $this->pageConfig->getTitle()->set(__('My watch List'));
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
     * @return mixed
     */
    public function getAuctions(){
        return $this->getCurrentBidder()->getWatchListAuctions();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {

        parent::_prepareLayout();
        if ($this->getAuctions()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setCollection(
                $this->getAuctions()
            );
            $this->setChild('pager', $pager);
            $this->getAuctions()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param int $auctionId
     * @return string
     */
    public function getRemovefromwatchlistUrl($auctionId)
    {
        return $this->getUrl('auction/index/removefromwatchlist', ['auction_id' => $auctionId,'watchlist_page'=>1]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}