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

use Magestore\Auction\Model\Auction;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class CreateAuctionBox extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var \Magestore\Auction\Model\ResourceModel\Bid\CollectionFactory
     */
    protected $_auctionFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory ;
    /**
     * Update constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Customer\Model\Session $seccion,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_auctionFactory = $auctionFactory;
        $this->session = $seccion;
        $this->resultPageFactory  = $resultPageFactory;
    }

    /**
     * Execute action
     * @var \Magestore\Auction\Model\Auction $aution
     */
    public function execute(){
        $product_id = $this->getRequest()->getParam('product_id');
        $result = $this->_resultJsonFactory->create();
        if($auction = $this->_auctionFactory->create()->loadByProductId($product_id)){
//            $this->_auctionFactory->create()->setBlockedGroupsId();
            $resultPage = $this->resultPageFactory->create();
            $blockInstance = $resultPage->getLayout()->createBlock('Magestore\Auction\Block\Auction')
                ->setAuction($auction)
                ->setBlockedGroupsId($this->getRequest()->getParam('blocked_groups_id'));
            $result->setData([
                'success' => true,
                'html'    => $blockInstance->toHtml(),
                'refresh' => $auction->getIsChangedStatus()
            ]);
        }else {
            $result->setData(['success' => false ]);
        }
        return $result;
    }
}