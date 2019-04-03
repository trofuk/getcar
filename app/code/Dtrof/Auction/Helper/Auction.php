<?php
namespace Magestore\Auction\Helper;

use Magento\Framework\App\ObjectManager;
use \Magestore\Auction\Model\Auction as AuctionModel;
use \Magestore\Auction\Model\ResourceModel\Auction\CollectionFactory as AuctionCollectionFactory;
use \Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Auction extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magestore\Auction\Model\ResourceModel\Auction\CollectionFactory
     */
    protected $_auctionCollectionFactory;

    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;

    /**
     * @var \Magestore\Auction\Model\PackagesAuctions
     */
    protected $_packagesAuctions;

    protected $_priceCurrency;

    protected $_bid;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        AuctionCollectionFactory $auctionCollectionFactory,
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magestore\Auction\Model\PackagesAuctions $packagesAuctions,
        \Magestore\Auction\Model\Bid $bid,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->_auctionCollectionFactory = $auctionCollectionFactory;
        $this->_auctionFactory = $auctionFactory;
        $this->_packagesAuctions = $packagesAuctions;
        $this->_priceCurrency = $priceCurrency;
        $this->_bid = $bid;
    }

    public function getAuctionByProductId($productId)
    {
        $array = [];
        if($this->getAuctionId($productId) > 0) {
            $_auction = $this->_auctionFactory->create()->load($this->getAuctionId($productId));
            if(!empty($_auction->getData())) {
                $array['auction_id'] = $_auction->getAuctionId();
                $array['product_id'] = $_auction->getProductId();
                $array['name'] = $_auction->getName();
                $array['start_time'] = $_auction->getStartTime();
                $array['end_time'] = $_auction->getEndTime();
                $array['price'] = $_auction->getCurrentPrice();
                $timeLeft = $_auction->getTimeLeft();
                $timeToStart = $_auction->getTimeToStart();
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
                $array['countdown'] = [
                    'time' => $time,
                    'class' => $class,
                    'type' => $type,
                ];
            }
        }
        return $array;
    }

    public function getCurrentPrice($productId)
    {

        if($this->getAuctionId($productId) > 0) {
            $_auction = $this->_auctionFactory->create()->load($this->getAuctionId($productId));
            return $_auction->getCurrentPrice();
        } else {
//            $this->_bid->
        }
    }

    protected function getAuctionId($productId)
    {
        $auction = $this->_packagesAuctions->getAuctionByProductId($productId);
        if(isset($auction['auction_id'])) {
            return $auction['auction_id'];
        } else {
            return 0;
        }
    }

    public function getCurrentPriceFormatCurrency($productId) {
        return $this->getFormatCurrency($this->getCurrentPrice($productId));
    }

    public function getFormatCurrency($price,
                                      $includeContainer = true,
                                      $precision = PriceCurrencyInterface::DEFAULT_PRECISION)
    {
        return $this->_priceCurrency->format($price, $includeContainer, $precision);
    }

    /**
     * @return bool
     */
    public function isLoggedIn(){
        $productId = $this->_request->getParam('product_id');
        return $this->getCurrentBidder()->isLoggedIn($productId);
    }

    /**
     * @return \Magestore\Auction\Model\Bidder
     */
    public function getCurrentBidder(){
        $bidderFactory = ObjectManager::getInstance()->create('\Magestore\Auction\Model\BidderFactory');
        $auction = $bidderFactory->create()->getCurrentBidder();
        return $auction;
//        $this->setData('current_bidder', $auction);
    }
}