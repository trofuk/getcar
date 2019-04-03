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
class Bid extends \Magestore\Auction\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Auction::viewbids.phtml';

    /**
     * @var \Magestore\Auction\Model\Bid
     */
    protected $_bidFactory;

    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;
    /**
     * @var \Magestore\Auction\Model\ResourceModel\Bid\
     */
    protected $bids;
    /**
     * @var Session
     */
    protected $session;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param \Magestore\Auction\Model\BidFactory $bidFactory
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magestore\Auction\Block\Context $context,
        \Magestore\Auction\Model\BidFactory $bidFactory,
        \Magestore\Auction\Model\AuctionFactory $_auctionFactory,
        Session $customerSession,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->_bidFactory = $bidFactory;
        $this->_auctionFactory = $_auctionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('List Bids'));
    }

    /**
     * @return \Magestore\Auction\Model\Auction
     */
    public function getCurrentAuction(){
        $auction = $this->_auctionFactory->create()->load($this->getRequest()->getParam('auction_id'));
        $dateTimeNow = date('Y-m-d H:i:s');
        if($auction->getStartTime()< $dateTimeNow && $auction->getEndTime() <= $dateTimeNow){
            $auction->updateStatus()->setStoreViewId(0)->save();
        }
        return $auction;
    }

    /**
     * @return \Magestore\Auction\Model\ResourceModel\Bid\Collection
     */
    public function getBids(){
        $collection = $this->getCurrentAuction()
            ->getBids()
            ->setPageSize(10)
            ->setCurPage(1);
//            ->setOrder('status',\Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        if($this->getRequest()->getParam('p')){
            $collection->setCurPage((int)$this->getRequest()->getParam('p'));
        }
        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getBids()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setCollection(
                $this->getBids()
            );
            $this->setChild('pager', $pager);
            $this->getBids()->load();
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
     * @param object $bid
     * @return string
     */
    public function getCancelBid($bid)
    {
        return $this->getUrl('auction/index/cancelbid', ['id' => $bid->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}