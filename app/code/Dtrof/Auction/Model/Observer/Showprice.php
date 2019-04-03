<?php
namespace Magestore\Auction\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model;

class Showprice implements ObserverInterface
{
    /**
     * @var \Magestore\Auction\Model\SystemConfig
     */
    protected $_auctionConfig;

    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidderFactory;
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_autionFactory;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * Updateprice constructor.
     * @param \Magestore\Auction\Model\SystemConfig $auctionConfig
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     */
    public function __construct(
        \Magestore\Auction\Model\SystemConfig $auctionConfig,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Checkout\Model\Session $session,
        \Magestore\Auction\Model\BidderFactory $bidderFactory
    ) {
        $this->_auctionConfig = $auctionConfig;
        $this->_autionFactory = $auctionFactory;
        $this->_checkoutSession = $session;
        $this->_bidderFactory = $bidderFactory;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $item = $observer->getProduct()->setData('final_price',20);

    }

    public function isWinProduct($productId){
        return true;
    }

    protected function _hasAuctionItem($bidId){
        $items = $this->_checkoutSession->getQuote()->getAllItems();
        foreach($items as $_item){
            if($_item->getOptionByCode('bid_id')&&$_item->getOptionByCode('bid_id')->getValue()==$bidId){
                return true;
            };
        }
        return false;
    }
    public function getQuote()
    {
        if (!$this->getData('quote')) {
            $this->setData('quote', $this->_checkoutSession->getQuote());
        }
        return $this->getData('quote');
    }
}
