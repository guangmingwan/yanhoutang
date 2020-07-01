<?php
/**
 * Copyright © 2019 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Head\Json;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Product extends \MageWorx\SeoMarkup\Block\Head\Json
{
    const IN_STOCK     = 'http://schema.org/InStock';
    const OUT_OF_STOCK = 'http://schema.org/OutOfStock';
    const OFFER        = 'http://schema.org/Offer';

    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Product
     */
    protected $helperDataProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $helperCatalog;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct
     * @param \Magento\Catalog\Helper\Data $helperCatalog
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct,
        \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct,
        \Magento\Catalog\Helper\Data $helperCatalog,
        \Magento\Framework\View\Element\Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->registry           = $registry;
        $this->helperProduct      = $helperProduct;
        $this->helperDataProvider = $dataProviderProduct;
        $this->helperCatalog      = $helperCatalog;
        $this->priceCurrency      = $priceCurrency;

        parent::__construct($context, $data);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getMarkupHtml()
    {
        $html            = '';
        $productJsonData = [];

        if ($this->helperProduct->isRsEnabled()) {
            $productJsonData[] = $this->getJsonProductData();
        }

        if ($this->helperProduct->isGaEnabled()) {
            $productJsonData[] = $this->getGoogleAssistantJsonData();
        }

        $productJson = !empty($productJsonData) ? json_encode($productJsonData) : '';

        if ($productJson) {
            $html .= '<script type="application/ld+json">' . $productJson . '</script>';
        }

        return $html;
    }

    /**
     *
     * @return array
     */
    protected function getJsonProductData()
    {
        $product = $this->registry->registry('current_product');

        if (!$product) {
            return [];
        }

        $this->_product = $product;

        $data                = [];
        $data['@context']    = 'http://schema.org';
        $data['@type']       = 'Product';
        $data['name']        = $this->_product->getName();
        $data['description'] = $this->helperDataProvider->getDescriptionValue($this->_product);
        $data['image']       = $this->helperDataProvider->getProductImage($this->_product)->getImageUrl();

        $offers = $this->getOfferData();
        if (!empty($offers['price']) || !empty($offers[0]['price'])) {
            $data['offers'] = $offers;
        }

        $aggregateRatingData = $this->helperDataProvider->getAggregateRatingData($this->_product, false);

        if (!empty($aggregateRatingData)) {
            $aggregateRatingData['@type'] = 'AggregateRating';
            $data['aggregateRating']      = $aggregateRatingData;
        }

        /**
         * Google console error: "Either 'offers', 'review' or 'aggregateRating' should be specified"
         */
        if ($this->helperProduct->isRsEnabledForSpecificProduct() === false
            && empty($data['aggregateRating'])
            && empty($data['offers'])
        ) {
            return [];
        }

        if (!empty($data['aggregateRating']) && $this->helperProduct->isReviewsEnabled()) {
            $reviewData= $this->helperDataProvider->getReviewData($this->_product, false);

            if (!empty($reviewData)) {
                $data['review'] = $reviewData;
            }
        }

        $productIdValue = $this->helperDataProvider->getProductIdValue($this->_product);

        if ($productIdValue) {
            $data['productID'] = $productIdValue;
        }

        $color = $this->helperDataProvider->getColorValue($this->_product);
        if ($color) {
            $data['color'] = $color;
        }

        $brand = $this->helperDataProvider->getBrandValue($this->_product);
        if ($brand) {
            $data['brand'] = $brand;
        }

        $manufacturer = $this->helperDataProvider->getManufacturerValue($this->_product);
        if ($manufacturer) {
            $data['manufacturer'] = $manufacturer;
        }

        $model = $this->helperDataProvider->getModelValue($this->_product);
        if ($model) {
            $data['model'] = $model;
        }

        $gtin = $this->helperDataProvider->getGtinData($this->_product);
        if (!empty($gtin['gtinType']) && !empty($gtin['gtinValue'])) {
            $data[$gtin['gtinType']] = $gtin['gtinValue'];
        }

        $skuValue = $this->helperDataProvider->getSkuValue($this->_product);
        if ($skuValue) {
            $data['sku'] = $skuValue;
        }

        $weightValue = $this->helperDataProvider->getWeightValue($this->_product);
        if ($weightValue) {
            $data['weight'] = $weightValue;
        }

        $categoryName = $this->helperDataProvider->getCategoryValue($this->_product);
        if ($categoryName) {
            $data['category'] = $categoryName;
        }

        $customProperties = $this->helperProduct->getCustomProperties();

        if ($customProperties) {
            foreach ($customProperties as $propertyName => $propertyValue) {
                if (!$propertyName || !$propertyValue) {
                    continue;
                }
                $value = $this->helperDataProvider->getCustomPropertyValue($product, $propertyValue);
                if ($value) {
                    $data[$propertyName] = $value;
                }
            }
        }

        return $data;
    }

    /**
     *
     * @return array
     */
    protected function getOfferData()
    {
        $data = [];

        if ($this->helperProduct->useMultipleOffer()
            && $this->_product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
            $productType = $this->_product->getTypeInstance();

            $children = $productType->getUsedProducts($this->_product);

            /** @var \Magento\Catalog\Model\Product $child */
            foreach ($children as $child) {
                $data[] = $this->getChildProductOfferData($child, $this->_product);
            }

        } else {
            $data['@type'] = self::OFFER;
            $data['price'] = $this->getPrice();

            $data['url']           = $this->_product->getProductUrl();
            $data['priceCurrency'] = $this->helperDataProvider->getCurrentCurrencyCode();

            if ($this->helperDataProvider->getAvailability($this->_product)) {
                $data['availability'] = self::IN_STOCK;
            } else {
                $data['availability'] = self::OUT_OF_STOCK;
            }

            $priceValidUntil = $this->helperDataProvider->getPriceValidUntilValue($this->_product);

            if ($priceValidUntil) {
                $data['priceValidUntil'] = $priceValidUntil;
            }

            $condition = $this->helperDataProvider->getConditionValue($this->_product);
            if ($condition) {
                $data['itemCondition'] = $condition;
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @return array
     */
    protected function getChildProductOfferData($product, $parentProduct)
    {
        $data                  = [];
        $data['@type']         = self::OFFER;
        $data['price']         = $this->getPrice($product);
        $data['url']           = $parentProduct->getProductUrl();
        $data['priceCurrency'] = $this->helperDataProvider->getCurrentCurrencyCode();

        if ($this->helperDataProvider->getAvailability($product)) {
            $data['availability'] = self::IN_STOCK;
        } else {
            $data['availability'] = self::OUT_OF_STOCK;
        }

        $priceValidUntil = $this->helperDataProvider->getPriceValidUntilValue($product);

        if ($priceValidUntil) {
            $data['priceValidUntil'] = $priceValidUntil;
        }

        $condition = $this->helperDataProvider->getConditionValue($product);
        if ($condition) {
            $offer['itemCondition'] = $condition;
        }

        $data['sku']  = $this->helperDataProvider->getProductIdValue($product);
        $data['name'] = $product->getName();

        return $data;
    }

    /**
     * Method getFinalPries() doesn't work for switched currency (internal issue - SM:18)
     * @param \Magento\Catalog\Model\Product|null
     * @return float
     */
    protected function getPrice($product = null)
    {
        $product = $product ? $product : $this->_product;

        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->__toString();

        if ($price) {
            $price = $this->priceCurrency->round($price);
        }

        return $price;
    }

    /**
     * @return array
     */
    protected function getGoogleAssistantJsonData()
    {
        $data['@context']         = 'http://schema.org/';
        $data['@type']            = 'WebPage';
        $speakable                = [];
        $speakable['@type']       = 'SpeakableSpecification';
        $speakable['cssSelector'] = explode(',', $this->helperProduct->getGaCssSelectors());
        $speakable['xpath']       = ['/html/head/title'];
        $data['speakable']        = $speakable;

        return $data;
    }
}