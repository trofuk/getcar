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
 * @package     Magestore_Pdfinvoiceplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Auction\Controller\Index;
/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;
    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidder;

    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_config;
    /**
     * Cancel constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Auction\Model\BidderFactory $bidderFactory
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Auction\Model\BidderFactory $bidderFactory,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magestore\Auction\Model\SystemConfig $systemConfig
    ) {
        parent::__construct($context);
        $this->_auctionFactory = $auctionFactory;
        $this->_bidder = $bidderFactory;
        $this->_config = $systemConfig;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        if(!$this->_bidder->create()->isLoggedIn()&&!$this->_config->cancelBid()){
            $this->messageManager->addError(__('You are unable to cancel this bid.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('auction/index/mybids');
            return $resultRedirect;
        }
        $bidder = $this->_bidder->create()->getCurrentBidder();
        $cancel = $this->_auctionFactory->create()->cancelBid($this->getRequest()->getParam('id'),$bidder->getCustomerId());
        if($cancel){
            $this->messageManager->addSuccess(__('The bid has been canceled.'));
        }else{
            $this->messageManager->addError(__('You are unable to cancel this bid.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('auction/index/mybids');
        return $resultRedirect;
    }
}