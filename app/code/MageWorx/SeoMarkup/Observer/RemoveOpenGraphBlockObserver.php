<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreResolver;

class RemoveOpenGraphBlockObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * RemoveOpenGraphBlockObserver constructor.
     *
     * @param StoreResolver $storeResolver
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     */
    public function __construct(
        StoreResolver $storeResolver,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct
    ) {
        $this->storeResolver = $storeResolver;
        $this->helperProduct = $helperProduct;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $observer->getBlock();

        if ($block->getNameInLayout() == 'opengraph.general'
            && $this->helperProduct->isOgEnabled($this->storeResolver->getCurrentStoreId())
        ) {
            $block->setTemplate('');
        }
    }
}
