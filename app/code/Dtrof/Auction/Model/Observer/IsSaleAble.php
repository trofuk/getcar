<?php
namespace Magestore\Auction\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model;

class IsSaleAble implements ObserverInterface
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
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProduct
    ) {

        $this->_auctionFactory = $auctionFactory;
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_configurableProduct = $configurableProduct;
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
        $links = $this->_request->getRouteName() . $this->_request->getControllerName() . $this->_request->getActionName();
        if ($links != 'catalogcategoryview' && $links != 'catalogsearchresultindex') {
            $sale = $this->_registry->registry('Auction_Products_Is_Sale_Albe');
            if(!$sale){
                $sale = [];
            }

            $productId = $observer->getProduct()->getId();
            if(in_array($productId,$sale)){
                return $observer;
            }

            $not_sale = $this->_registry->registry('Auction_Products_Is_Not_Sale_Albe');
            if(!$not_sale){
                $not_sale = [];
            }
            if(in_array($productId,$not_sale)){
                $observer->getSalable()->setIsSalable(false);
                return $observer;
            }

            $parentProducts = $this->_configurableProduct->getParentIdsByChild($productId);
            if(count($parentProducts)){
                $isSaleAble = $this->_auctionFactory->create()->checkIsSaleAle($parentProducts[0]);
                $childProducts = $this->_configurableProduct->getChildrenIds($parentProducts[0]);
                if($isSaleAble){
                    if(isset($childProducts[0])){
                        foreach ($childProducts[0] as $_productId) {
                            $sale[] = $_productId;
                        }
                    }
                    $sale[] = $parentProducts[0];
                    $this->_registry->unregister('Auction_Products_Is_Sale_Albe');
                    $this->_registry->register('Auction_Products_Is_Sale_Albe',$sale);
                    return $observer;
                }else{
                    if(isset($childProducts[0])){
                        foreach($childProducts[0] as $_productId){
                            $not_sale[] = $_productId;
                        }
                    }
                    $not_sale[] = $parentProducts[0];
                    $this->_registry->unregister('Auction_Products_Is_Not_Sale_Albe');
                    $this->_registry->register('Auction_Products_Is_Not_Sale_Albe',$not_sale);
                    $observer->getSalable()->setIsSalable(false);
                    return $observer;
                }
            }else{
                $isSaleAble = $this->_auctionFactory->create()->checkIsSaleAle($productId);
                if($isSaleAble){
                    if($observer->getProduct()->getTypeId()=='configurable'){
                        $childProducts = $this->_configurableProduct->getChildrenIds($productId);
                        foreach($childProducts as $_productId){
                            $sale[] = $_productId;
                        }
                    }
                    $sale[] = $productId;
                    $this->_registry->unregister('Auction_Products_Is_Sale_Albe');
                    $this->_registry->register('Auction_Products_Is_Sale_Albe',$sale);
                    return $observer;
                }else{
                    if($observer->getProduct()->getTypeId()=='configurable'){
                        $childProducts = $this->_configurableProduct->getChildrenIds($productId);
                        foreach($childProducts as $_productId){
                            $not_sale[] = $_productId;
                        }
                    }
                    $not_sale[] = $productId;
                    $this->_registry->unregister('Auction_Products_Is_Not_Sale_Albe');
                    $this->_registry->register('Auction_Products_Is_Not_Sale_Albe',$not_sale);
                    $observer->getSalable()->setIsSalable(false);
                    return $observer;
                }
            }
        }
        return $observer;
    }
}
