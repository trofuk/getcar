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

namespace Magestore\Auction\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * @category Magestore
 * @package  Magestore_Auction
 * @module   Auction
 * @author   Magestore Developer
 */
class SystemConfig
{
    const XML_PATH_IS_ENABLE_MODULE = 'auction/general/enable';
    const XML_PATH_WON_MESSAGE      = 'auction/general/won_message';
    const XML_PATH_UPDATE_TIME      = 'auction/general/delay_time';
    const XML_PATH_SHOW_PRICE       = 'auction/general/show_price';
    const XML_PATH_AUTOBID          = 'auction/autobid/enable_autobid';
    const XML_PATH_BIDDERS_NAME_TYPE= 'auction/auction_separator_biddername/bidder_name_type';
    const XML_PATH_NAME_PREFIX      = 'auction/auction_separator_biddername/bidder_name_prefix';
    const XML_PATH_CANCEL_BID       = 'auction/general/cancel_bid';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * SystemConfig constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $path
     * @param string $scopeType
     * @param null $store
     *
     * @return mixed
     */
    public function getConfig($path, $scopeType = ScopeInterface::SCOPE_STORE, $store = null)
    {
        if ($store === null) {
            $store = $this->_storeManager->getStore()->getId();
        }
        return $this->_scopeConfig->getValue(
            $path,
            $scopeType,
            $store
        );
    }
    /**
     * Check enable frontend.
     *
     * @return string
     */
    public function isEnable()
    {
        return $this->getConfig(self::XML_PATH_IS_ENABLE_MODULE);
    }

    /**
     * @return string
     */
    public function wonMessage(){
        return $this->getConfig(self::XML_PATH_WON_MESSAGE);
    }

    /**
     * @return mixed
     */
    public function updateTime(){
        return $this->getConfig(self::XML_PATH_UPDATE_TIME);
    }

    /**
     * @return mixed
     */
    public function showPrice(){
        return $this->getConfig(self::XML_PATH_SHOW_PRICE);
    }
    /**
     * @return mixed
     */
    public function autobid(){
        return $this->getConfig(self::XML_PATH_AUTOBID);
    }
    /**
     * @return mixed
     */
    public function bidderNameType(){
        return $this->getConfig(self::XML_PATH_BIDDERS_NAME_TYPE);
    }
    /**
     * @return mixed
     */
    public function bidderNamePrifix(){
        return $this->getConfig(self::XML_PATH_NAME_PREFIX);
    }
    /**
     * @return mixed
     */
    public function cancelBid(){
        return $this->getConfig(self::XML_PATH_CANCEL_BID);
    }

    /**
     * @return bool
     */
    public function isShowTopLink(){
        return true;
    }


}
