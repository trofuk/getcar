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

use Magento\Framework\Controller\ResultFactory;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Execute action
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Auction\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magestore\Auction\Model\Auction
     */
    protected $_auctionFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * ViewAuction constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Auction\Helper\Data $helper
     * @param \Magestore\Auction\Model\SystemConfig $systemConfig
     * @param \Magestore\Auction\Model\Auction $auctionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Auction\Helper\Data $helper,
        \Magestore\Auction\Model\SystemConfig $systemConfig,
        \Magestore\Auction\Model\Auction $auctionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_systemConfig = $systemConfig;
        $this->_auctionFactory = $auctionFactory;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @return \Magestore\Auction\Model\Auction
     */
    public function _initAuction()
    {
        try {
            $auction = $this->_auctionFactory->updateStatusMultiAuctions()->getProcessingAuctionsInfo();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $this->_coreRegistry->register('current_auction', $auction);
        $this->_coreRegistry->register('auction_show_normal_price', $this->_systemConfig->showPrice());
        return $auction;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $this->_initAuction();
        $this->layerResolver->create('category');
        if (!$this->_systemConfig->isEnable()) {
            return $this->resultRedirectFactory->create()->setPath('');
        }
        if (!$this->_coreRegistry->registry('current_category')) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $category->load($this->_storeManager->getStore()->getRootCategoryId());
            $category->setName(__('Auctions'));
            $this->_coreRegistry->register('current_category', $category);
        }
        $resultPage->getConfig()->getTitle()->set(__('Auctions'));
        return $resultPage;
    }
}