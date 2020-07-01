<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Plugin\ProductList;

class AddAttributesToProductListPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Category
     */
    protected $helperCategory;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     * AddAttributesToProductListPlugin constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \MageWorx\SeoMarkup\Helper\Category $helperCategory
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \MageWorx\SeoMarkup\Helper\Category $helperCategory,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct
    ) {
        $this->request        = $request;
        $this->helperCategory = $helperCategory;
        $this->helperProduct  = $helperProduct;
    }

    /**
     * @param \Magento\Catalog\Model\Layer $subject
     * @param \Magento\Catalog\Model\Layer $result
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function afterPrepareProductCollection($subject, $result, $collection)
    {
        if ($this->request->getFullActionName() !== 'catalog_category_view') {
            return;
        }

        if (!$this->helperCategory->isRsEnabled()) {
            return;
        }

        if ($this->helperProduct->isBrandEnabled()) {
            $brandCode = $this->helperProduct->getBrandCode();

            if ($brandCode) {
                $collection->addAttributeToSelect($brandCode);
            }
        }

        if ($this->helperProduct->isColorEnabled()) {
            $colorCode = $this->helperProduct->getColorCode();

            if ($colorCode) {
                $collection->addAttributeToSelect($colorCode);
            }
        }

        if ($this->helperProduct->isGtinEnabled()) {
            $gtinCode = $this->helperProduct->getGtinCode();

            if ($gtinCode) {
                $collection->addAttributeToSelect($gtinCode);
            }
        }

        if ($this->helperProduct->isManufacturerEnabled()) {
            $manufacturerCode = $this->helperProduct->getManufacturerCode();

            if ($manufacturerCode) {
                $collection->addAttributeToSelect($manufacturerCode);
            }
        }

        if ($this->helperProduct->isSkuEnabled()) {
            $skuCode = $this->helperProduct->getSkuCode();

            if ($skuCode) {
                $collection->addAttributeToSelect($skuCode);
            }
        }

        if ($this->helperProduct->isConditionEnabled()) {
            $conditionCode = $this->helperProduct->getConditionCode();

            if ($conditionCode) {
                $collection->addAttributeToSelect($conditionCode);
            }
        }

        if ($this->helperProduct->isModelEnabled()) {
            $modelCode = $this->helperProduct->getModelCode();

            if ($modelCode) {
                $collection->addAttributeToSelect($modelCode);
            }
        }

        $descriptionCode = $this->helperProduct->getDescriptionCode();

        if ($descriptionCode) {
            $collection->addAttributeToSelect($descriptionCode);
        }

        $customProperties = $this->helperProduct->getCustomProperties();

        foreach ($customProperties as $propertyCode) {
            $collection->addAttributeToSelect($propertyCode);
        }

        return;
    }
}