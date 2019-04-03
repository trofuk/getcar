<?php


namespace Magestore\Auction\Helper;

class AuctionConfig extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_autobid;

    protected $_backendUrl;

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magestore\Auction\Model\SystemConfig $config,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->_storeManager = $storeManager;
        $this->_autobid = $config;
    }

//    public function __construct()
//    {
//     echo 'dfsdbsjadbjskabdas';
//    }


    public function isEnableAutobid()
    {
        return $this->_autobid->getConfig('auction/autobid/enable_autobid');
    }

}
