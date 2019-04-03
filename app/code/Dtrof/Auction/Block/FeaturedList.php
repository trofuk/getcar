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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

class FeaturedList extends \Magento\Catalog\Block\Product\ListProduct {

    /**
     * Product collection model
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_collection;

    /**
     * Product collection model
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * Image helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Catalog Layer
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_scopeConfig1;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;

    /**
     * FeaturedList constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magestore\Auction\Model\SystemConfig $scopeConfig
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magestore\Auction\Model\SystemConfig $scopeConfig,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        array $data = []
    ) {
        $this->imageBuilder = $context->getImageBuilder();
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_collection = $collection;
        $this->_imageHelper = $context->getImageHelper();
        $this->_scopeConfig1 = $scopeConfig;
        $this->_auctionFactory = $auctionFactory;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * Get product collection
     */
    protected function getProducts() {
        $collection = $this->_collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('news_from_date')
                ->addAttributeToSelect('news_to_date')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addFieldToFilter('entity_id', ['in' => $this->getFeatureProductIds()]);
        $collection->getSelect()
                ->order('rand()');
        // Set Pagination Toolbar for list page
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'featuredpro.grid.record.pager')->setLimit(8)->setCollection($collection);
        $this->setChild('pager', $pager); // set pager block in layout
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    /**
     * @return array
     */
    public function getFeatureAuction(){
        if(!$this->getData('features_auction')){
            $this->setData('features_auction',$this->_auctionFactory->create()->getFeatureAuctionsInfo());
        }
        return $this->getData('features_auction');
    }

    public function getFeatureProductIds(){
        return array_keys($this->getFeatureAuction());
    }
    /*
     * Load and return product collection 
     */

    public function getLoadedProductCollection() {
        return $this->getProducts();
    }

    /**
     * @return string
     */
    public function getToolbarHtml() {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getMode() {
        return 'grid';
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper() {
        return $this->_imageHelper;
    }

    /**
     * @return string
     */
    public function getSectionStatus() {
        return $this->_scopeConfig1->isEnable();
    }

    /**
     * @return int
     */
    public function getProductLimit() {
        return 10;
    }

    /**
     * @return string
     */
    public function getPageTitle() {
        return __('Feature Auctions');
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product) {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

}
