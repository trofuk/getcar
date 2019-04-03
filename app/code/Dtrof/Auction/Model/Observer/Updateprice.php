<?php
namespace Magestore\Auction\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model;

class Updateprice implements ObserverInterface
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
        \Magestore\Auction\Model\BidderFactory $bidderFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_auctionConfig = $auctionConfig;
        $this->_autionFactory = $auctionFactory;
        $this->_checkoutSession = $session;
        $this->_bidderFactory = $bidderFactory;
        $this->_request = $request;
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
        $bidder = $this->_bidderFactory->create();
        if(!$bidder->isLoggedIn()){
            return;
        }
        $bidder = $bidder->getCurrentBidder();
        $item = $observer->getItem();
        $productId = ($item->getParentItem())?$item->getParentItem()->getProduct()->getId():$item->getProduct()->getId();
        $auction = $bidder->getWonInfo($productId);
        if($auction->getBidId()){
            if($item->getOptionByCode('bid_id')==null&&!$this->_hasAuctionItem($auction->getBidId())){
                $item->setData(\Magento\Quote\Api\Data\CartItemInterface::KEY_QTY,1);
                $item->setCustomPrice($auction->getCurrentPrice());
                $item->setOriginalCustomPrice($auction->getCurrentPrice());
                $item->addOption([
                        'label' => 'Auction',
                        'product_id' => $item->getProduct()->getId(),
                        'code' => 'bid_id',
                        'value' => $auction->getBidId()
                    ]
                );
            }elseif($item->getOptionByCode('bid_id')&&$item->getOptionByCode('bid_id')->getValue()!=0){
                $item->setData(\Magento\Quote\Api\Data\CartItemInterface::KEY_QTY,1);
                $item->setCustomPrice($auction->getCurrentPrice());
                $item->setOriginalCustomPrice($auction->getCurrentPrice());
            }else{
                $item->addOption([
                        'label' => 'Auction',
                        'product_id' => $item->getProduct()->getId(),
                        'code' => 'bid_id',
                        'value' => 0
                    ]
                );
            }
        }
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
