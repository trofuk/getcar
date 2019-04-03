<?php
namespace Magestore\Auction\Model\Source;

class Showprice {
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>__('Yes')),
            array('value'=>0, 'label'=>__('No')),
        );
    }
}