<?php
namespace Magestore\Auction\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerPackage implements ObserverInterface
{
    /**
     * @var \Magestore\Auction\Model\AuctionFactory
     */
    protected $_auctionFactory;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * IsSaleAble constructor.
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     */
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_configurableProduct;

    /**
     * IsSaleAble constructor.giacs cuar nos
     * @param \Magestore\Auction\Model\AuctionFactory $auctionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProduct
     */
    public function __construct(
        \Magestore\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Framework\App\Request\Http $request
    ) {

        $this->_auctionFactory = $auctionFactory;
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
    public function execute(\Magento\Framework\Event\Observer $observer){

        $this->_request->getParam('id');
        $data = $this->_request->getPost();
//        var_dump($data); die;
        return $observer;
    }
}
