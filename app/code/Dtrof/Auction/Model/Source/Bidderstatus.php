<?php
namespace Magestore\Auction\Model\Source;

class Bidderstatus{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>__('Enable')),
            array('value'=>2, 'label'=>__('Disable')),
        );
    }
}