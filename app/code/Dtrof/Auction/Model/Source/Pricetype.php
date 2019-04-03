<?php

namespace Magestore\Auction\Model\Source;

class Pricetype{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>__('Excluding Tax')),
            array('value'=>1, 'label'=>__('Including Tax')),
        );
    }
}