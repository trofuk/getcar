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

namespace Magestore\Auction\Plugin\Aggregation\Category;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class DataProvider
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var Config
     */
    private $eavConfig;


    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Resolver $layerResolver
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\Registry $registry,
        Config $eavConfig
    ) {
        $this->_resource = $resource;
        $this->_coreRegistry = $registry;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Plugin\Aggregation\Category\DataProvider $subject
     * @param $result
     *
     * @return Select
     */
    public function aroundAroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Plugin\Aggregation\Category\DataProvider $pluginAggregationDataProvider,
        \Closure $pluginAggregationDataProviderProceed,
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $aggregationDataProvider,
        \Closure $aggregationDataProviderProceed,
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $result = $pluginAggregationDataProviderProceed(
            $aggregationDataProvider,
            $aggregationDataProviderProceed,
            $bucket,
            $dimensions,
            $entityIdsTable
        );
        $auction = $this->_coreRegistry->registry('current_auction');
        /* @var \Magento\Framework\DB\Select $result */
        if (is_array($auction)) {
            /** @var \Magestore\Auction\Model\Auction $auction */
            if ($bucket->getField() != 'category_ids') {
                $result->where('main_table.entity_id IN(?)', array_keys($auction));
            }
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());
            if ($attribute->getAttributeCode() == 'category_gear'
                || $attribute->getAttributeCode() == 'activity'
            ) {
                $result->joinInner(
                    ['price_index' => $this->_resource->getTableName('catalog_product_index_price')],
                    'price_index.entity_id  = main_table.entity_id',
                    []
                )->group('price_index.entity_id');
            }
        }
        return $result;
    }
}