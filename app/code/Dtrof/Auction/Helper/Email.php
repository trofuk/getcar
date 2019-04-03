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

namespace Magestore\Auction\Helper;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
/**
 * Helper Data.
 * @category Magestore
 * @package  Magestore_Storepickup
 * @module   Storepickup
 * @author   Magestore Developer
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SALES_EMAIL_IDENTITY      = "trans_email/ident_sales";
    const XML_PATH_ADMIN_EMAIL_IDENTITY      = "trans_email/ident_general";
    const XML_PATH_SALES_EMAIL_IDENTITY_NAME = "trans_email/ident_sales/name";
    const NEW_BID_TO_ADMIN          = 'auction/emails/newbid_to_admin_email_template';
    const AUCTION_COMPLETED_TO_WATCHER = 'auction/emails/notice_auction_completed_towatcher';
    const NEW_AUTOBID_TO_BIDDER     = 'auction/emails/newautobid_to_bidder_email_template';
    const AUCTION_COMPLETED_TO_ADMIN= 'auction/emails/notice_auction_completed';
    const NEW_BID_TO_BIDDER         = 'auction/emails/newbid_to_bidder_email_template';
    const NEW_BID_TO_WATCHER        = 'auction/emails/newbid_to_watcher_email_template';
    const NOTICE_CANCELLATION       = 'auction/emails/notice_cancel_bid_email_template';
    const NOTICE_FAILDER            = 'auction/emails/notice_failder_email_template';
    const NOTICE_HIGHEST            = 'auction/emails/notice_highest_bid_email_template';
    const NOTICE_WINNER             = 'auction/emails/notice_winner_email_template';
    const OVER_AUTOBID_TO_BIDDER    = 'auction/emails/overautobid_to_bidder_email_template';
    const OVER_BID_TO_BIDDER        = 'auction/emails/overbid_to_bidder_email_template';
    const TEMPLATE_ID_NONE_EMAIL    = '0';
    const SECTION_CONFIG_AUCTION    = 'auction';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Email constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param Renderer $addressRenderer
     * @param \Magento\Payment\Helper\Data $paymentHelperData
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelperData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_paymentHelperData = $paymentHelperData;
        $this->_addressRenderer = $addressRenderer;
        $this->_objectManager = $objectManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_priceCurrency = $priceCurrency;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_logger = $context->getLogger();
        $this->_customerFactory = $customerFactory;
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
     * @param \Magestore\Auction\Model\Auction $auction
     * @return array
     */
    public function getWatcherInformation($auction){
        $customerIds = explode(',',$auction->getWatchList());
        if(count($customerIds)){
            $array = [];
            $customers = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('entity_id', ['in', $customerIds]);
            foreach($customers as $_customer){
                $array[] = ['name' => $_customer->getName(),'email' =>$_customer->getEmail()];
            }
            return $array;
        }
        return [];
    }


    /**
     * @param $auction
     */
    public function sendEmailAuctionCompletedToAdmin($auction) {
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::AUCTION_COMPLETED_TO_ADMIN);
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $this->getConfig(self::XML_PATH_ADMIN_EMAIL_IDENTITY),
        );

        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $auction->setAdminName($name_contact);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'auction' => $auction,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {
                \Zend_Debug::dump($ex->getMessage());
                exit;
            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param $auction
     */
    public function sendEmailAuctionCompletedToWatcher($auction) {
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::AUCTION_COMPLETED_TO_ADMIN);
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();

        $sendTo = $this->getWatcherInformation($auction);

        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $auction->setCustomerName($name_contact);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'auction' => $auction,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {
                \Zend_Debug::dump($ex->getMessage());
                exit;
            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param $bid
     */
    public function sendEmailToWinner($bid) {
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NOTICE_WINNER);
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'bid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param $bid
     */
    public function sendEmailNewBidToAdmin($bid) {
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NEW_BID_TO_ADMIN);
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $this->getConfig(self::XML_PATH_ADMIN_EMAIL_IDENTITY),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $bid->setAdminName($name_contact);
            $this->_logger->debug($item['email']);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'admin_name' => $name_contact,
                        'bid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Bid $bid
     */
    public function sendEmailNewBidToBidder($bid) {
        if(!$bid->getData('place_bid')){
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NEW_BID_TO_BIDDER);
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'bid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param $bid
     */
    public function sendEmailNewAutoBidToBidder($bid) {
        if(!$bid->getData('place_autobid')){
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NEW_AUTOBID_TO_BIDDER);
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'autobid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Bid $bid
     * @param \Magestore\Auction\Model\Auction $auction
     */
    public function sendEmailNewBidToWatcher($bid,$auction) {
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NEW_BID_TO_WATCHER );
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = $this->getWatcherInformation($auction);
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'auction_url' => $auction->getProductUrl(),
                        'watcher_name' => $name_contact,
                        'bid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Bid $bid
     */
    public function sendEmailCancelBid($bid) {
        if(!$bid->getData('cancel_bid')){
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NOTICE_CANCELLATION );
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $bid->setCustomerName($name_contact);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'bid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Auction $auction
     * @param \Magestore\Auction\Model\Bid $bid
     */
    public function sendEmailFailderBid($auction,$bid) {
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NOTICE_FAILDER );
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $auction->setCustomerName($name_contact);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'auction' => $auction,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Bid $bid
     * @param \Magestore\Auction\Model\Bid $higher_bid
     */
    public function sendEmailOverAutoBid($bid,$higher_bid,$aucton) {
        if(!$bid->getData('over_autobid')){
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::OVER_AUTOBID_TO_BIDDER );
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'auction' => $aucton,
                        'autobid' => $bid,
                        'higher_bid' => $higher_bid
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Bid $bid
     * @param \Magestore\Auction\Model\Bid $higher_bid
     */
    public function sendEmailOverBid($bid,$higher_bid,$auction_url) {
        if(!$bid->getData('over_bid')){
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::OVER_BID_TO_BIDDER );
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo(),
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $bid->setCustomerName($name_contact);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'customer_name' => $name_contact,
                        'auction_url' => $auction_url,
                        'bid' => $bid,
                        'higher_bid' => $higher_bid
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }

    /**
     * @param \Magestore\Auction\Model\Bid $bid
     */
    public function sendEmailHighest($bid) {
        if(!$bid->getData('highest_bid')){
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $template_id = $this->getConfig(self::NOTICE_HIGHEST );
        if ($template_id === self::TEMPLATE_ID_NONE_EMAIL) {
            return;
        }
        $this->inlineTranslation->suspend();
        $sendTo = array(
            $bid->getEmailInfo()
        );
        foreach ($sendTo as $item) {
            $email_contact = $item['email'];
            $name_contact = $item['name'];
            $bid->setCustomerName($name_contact);
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $template_id
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'bid' => $bid,
                    ]
                )->setFrom(
                    $this->scopeConfig->getValue(
                        self::XML_PATH_SALES_EMAIL_IDENTITY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->addTo(
                    $email_contact,
                    $name_contact
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {

            }
        }
        $this->inlineTranslation->resume();
    }



}
