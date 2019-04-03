<?php

namespace Magestore\Auction\Model\System\Templates;

class Newbidtobidder{

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_emailCollection;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_codeRegistry;

    /**
     * Auctioncompletedtowatcher constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collection
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collection
    )
    {
        $this->_codeRegistry = $registry;
        $this->_emailCollection = $collection;
    }

    public function toOptionArray()
    {
        if(!$collection = $this->_codeRegistry->registry('config_system_email_template')) {
            $collection = $this->_emailCollection->create()->load();
            $this->_codeRegistry->register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        array_unshift(
            $options,
            array(
                'value'=> 'magestore_auction_new_bid_tobidder',
                'label' => __('New bid notice to bidder (Default)')
            )
        );

        return $options;
    }
}