<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Auction\Model\Rewrite\PageCache;

/**
 * Builtin cache processor
 */
class Kernel extends \Magento\Framework\App\PageCache\Kernel
{
    /**
     * Modify and cache application response
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return void
     */
    public function process(\Magento\Framework\App\Response\Http $response)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $auction = $objectManager->get('Magestore\Auction\Block\Auction');
        if($auction->getCurrentAuction() && $auction->getCurrentAuction()->getId()){
            $this->cache->remove($this->identifier->getValue());
            return;
        }
        return parent::process($response);
    }
}
