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
use Magento\Catalog\Model\CategoryFactory;
use Magento\Customer\Model\Session;

/**
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class Slider extends \Magestore\Auction\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Auction::slider/index.phtml';
    /**
     * @var \Magestore\Auction\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;

    /**
     * @var
     */
    protected $_auctionCollectionFactory;


    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_config;

    /**
     * @var \Magestore\Auction\Model\PackagesCustomers
     */
    protected $_packagesCustomers;

    /**
     * @var \Magestore\Auction\Model\PackagesAuctions
     */
    protected $_packagesAuctions;

    /**
     * @var \Magestore\Auction\Model\Packages
     */
    protected $_packages;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Product\ImageFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * Slider constructor.
     * @param Context $context
     * @param \Magestore\Auction\Helper\Data $helper
     * @param \Magestore\Auction\Model\ResourceModel\Auction\CollectionFactory $auctionCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magestore\Auction\Block\Context $context,
        \Magestore\Auction\Helper\Data $helper,
        \Magestore\Auction\Model\ResourceModel\Auction\CollectionFactory $auctionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        Session $customerSession,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->_helper = $helper;
        parent::__construct($context, $data);
        $this->_auctionCollectionFactory = $auctionCollectionFactory;
        $this->_auctionFactory = $auctionFactory;
        $this->_productFactory = $productFactory;
        $this->_imageBuilder = $imageBuilder;
    }

    public function sliderForHomePage()
    {
        $collection = $this->_auctionCollectionFactory->create()->getAuctionForHomePageSlider();
        $array = [];
        foreach($collection as $_auction){
            $productId = $_auction->getProductId();
            $_product = $this->_productFactory->create()->load($productId);
            $array[$productId]['auction_id'] = $_auction->getAuctionId();
            $array[$productId]['product_id'] = $_auction->getProductId();
            $array[$productId]['name'] = $_auction->getName();
            $array[$productId]['start_time'] = $_auction->getStartTime();
            $array[$productId]['end_time'] = $_auction->getEndTime();
            $array[$productId]['price'] = $_auction->getCurrentPrice();
            $array[$productId]['url'] = $_auction->getProductUrl();
            $array[$productId]['image'] = $this->getImage($_product, 'product_page_image_large', 'image_url');
            $array[$productId]['countdown'] = $this->getTime($_auction->getAuctionId());
        }
        return $array;
    }

    public function getImage($product, $imageId, $key = '')
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create()->getData($key);
    }

    public function getTime($auctionId) {
        $auction = $this->_auctionFactory->create()->load($auctionId);
        $timeLeft = $auction->getTimeLeft();
        $timeToStart = $auction->getTimeToStart();
        if($timeToStart > 0) {
            $time = $timeToStart;
            $class = 'to-start';
            $type = __("To start");
        } else if($timeLeft > 0) {
            $time = $timeLeft;
            $class = 'to-finish';
            $type = __("To finish");
        } else {
            $time = 0;
            $class = '';
            $type = '';
        }
        return [
            'time' => $time,
            'class' => $class,
            'type' => $type,
        ];
    }

}
