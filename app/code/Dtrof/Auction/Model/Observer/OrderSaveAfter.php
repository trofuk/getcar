<?php
namespace Magestore\Auction\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Magestore\Auction\Model\BidderFactory
     */
    protected $_bidFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoterFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * OrderSaveAfter constructor.
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magestore\Auction\Model\BidFactory $bidFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_bidFactory = $bidFactory;
        $this->_coreRegistry = $registry;
        $this->_quoterFactory = $quoteFactory;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer){
        if (!$this->_coreRegistry->registry('check_transaction')) {
            $this->_coreRegistry->register('check_transaction', '1');
            $order = $observer->getEvent()->getOrder();
            $quoteId = $order->getQuoteId();
            $quote = $this->_quoterFactory->create()->load($quoteId);
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                $bidId = $item->getOptionByCode('bid_id');
                if ($bidId != null && $bidId->getValue() > 0) {
                    try {
                        $bid = $this->_bidFactory->create()->load($bidId->getValue());
                        $bid->setStatus(\Magestore\Auction\Model\Bid::BID_WON_AND_BOUGHT);
                        $bid->setOrderId($order->getId());
                        $bid->save();
                        $auction = $bid->getAuction();
                        if(!$auction->hasWinnerWaitToBuy()){
                            $auction->setStatus(\Magestore\Auction\Model\Auction::AUCTION_STATUS_CLOSED)
                                ->setStoreViewId(0)
                                ->save();
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }
    }
}
