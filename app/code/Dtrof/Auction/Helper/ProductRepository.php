<?php
namespace Magestore\Auction\Helper;

class ProductRepository extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_productRepo;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepo
    ) {
        $this->_productRepo = $productRepo;
        parent::__construct($context);
    }


    /**
     * Load product from productId
     *
     * @param $id
     * @return $this
     */
    public function getProductById($id)
    {
        return $this->_productRepo
            ->getById($id);
    }

    public function getAuctionAttribute($id)
    {
        $array = [];
        $product = $this->getProductById($id);
        if($product->getResource()->getAttribute('gc_mileage')) {
            $mileage = (int)$product->getGcMileage();
            if($mileage > 0) {
                $array['mileage'] = number_format($mileage, 0, '', ' ').' '.__('км');
            }
        }
        if($product->getResource()->getAttribute('gc_make')) {
            if($product->getAttributeText('gc_make') != '') {
                $array['brand'] = $product->getAttributeText('gc_make');
            }
        }
        if($product->getResource()->getAttribute('gc_model')) {
            if($product->getAttributeText('gc_model') != '') {
                $array['model'] = $product->getAttributeText('gc_model');
            }
        }
        if($product->getResource()->getAttribute('gc_year')) {
            if($product->getYear() != '') {
                $array['year'] = $product->getGcYear();
            }
        }
        if($product->getResource()->getAttribute('gc_enginecapacity')) {
            if($product->getGcEnginecapacity() != '') {
                $array['enginecapacity'] = $product->getGcEnginecapacity();
            }
        }
        if($product->getResource()->getAttribute('gc_lever')) {
            if($product->getAttributeText('gc_lever') != '') {
                $array['lever'] = $product->getAttributeText('gc_lever');
            }
        }
        if($product->getResource()->getAttribute('gc_gastype')) {
            if($product->getAttributeText('gc_gastype') != '') {
                $array['gastype'] = $product->getAttributeText('gc_gastype');
            }
        }
        if($product->getResource()->getAttribute('gc_carlocation')) {
            if($product->getAttributeText('gc_carlocation') != '') {
                $array['city'] = $product->getAttributeText('gc_carlocation');
            }
        }
        if($product->getResource()->getAttribute('gc_color')) {
            if($product->getGcColor() != '') {
                $array['color'] = $product->getGcColor();
            }
        }
        return $array;
    }

    public function showElements($id, $class="attribute", $type="span")
    {
        $string = '';
        $attributes = $this->getAuctionAttribute($id);
        foreach ($attributes as $key=>$attribute) {
            $string .= '<'.$type.' class="'.$class.' icon-'.$key.'">'.$attribute.'</'.$type.'>';
        }
        return $string;
    }
}