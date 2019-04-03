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

/**
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magestore\Auction\Helper\Image
     */
    protected $_imageHelper;


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * AbstractBlock constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magestore\Auction\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_systemConfig = $context->getSystemConfig();
        $this->_coreRegistry = $context->getCoreRegistry();
        $this->_objectManager = $context->getObjectManager();
    }

    /**
     * @return \Magestore\Auction\Model\SystemConfig
     */
    public function getSystemConfig()
    {
        return $this->_systemConfig;
    }

    /**
     * @return \\Magento\Framework\Registry
     */
    public function getCoreRegistry()
    {
        return $this->_coreRegistry;
    }


    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function storeManager()
    {
        return $this->_storeManager;
    }


    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
}
