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
 * @package     Magestore_Auction
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Auction\Block\Product;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Retrieve loaded category collection
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            /* @var $layer \Magento\Catalog\Model\Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')
                    ->getCategoryCollection()->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();
            $this->_productCollection->getSelect()->joinLeft(
                ['auction' => $this->_productCollection->getTable('magestore_auction')],
                'auction.product_id = e.entity_id AND auction.status IN (0,1)');
            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }
        return $this->_productCollection;
    }

    public function getTime($auctionId) {
        $object = \Magento\Framework\App\ObjectManager::getInstance();
        $auction = $object->create('\Magestore\Auction\Model\Auction')->load($auctionId);
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

    public function getAuctionProductPriceText($auctionId)
    {
        $object = \Magento\Framework\App\ObjectManager::getInstance();
        $auction = $object->create('\Magestore\Auction\Model\Auction')->load($auctionId);
        return $auction->getCurrentPriceText();
    }

    public function getAuctionProductPrice($auctionId)
    {
        $object = \Magento\Framework\App\ObjectManager::getInstance();
        $auction = $object->create('\Magestore\Auction\Model\Auction')->load($auctionId);
        return $auction->getCurrentPrice();
    }

    public function getLoadedProductCollection()
    {
        return $this->getProductCollection();
    }
}