<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\XmlSitemap\Model\ResourceModel\Catalog\ProductFactory;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;
use MageWorx\XmlSitemap\Model\Writer;
use Zend_Db_Statement_Exception;


/**
 * {@inheritdoc}
 */
class Product extends AbstractMediaGenerator
{
    /**
     * @var ProductFactory
     */
    protected $sitemapProductResourceFactory;

    /**
     * Product constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        ProductFactory $sitemapProductResourceFactory
    ) {
        $this->sitemapProductResourceFactory = $sitemapProductResourceFactory;
        parent::__construct($helper, $objectManager);
        $this->code = 'product';
        $this->name = __('Products');
    }

    /**
     * @param int $storeId
     * @param Writer $writer
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function generate($storeId, $writer)
    {
        $this->storeId = $storeId;
        $this->helper->init($this->storeId);
        $this->storeBaseUrl = $writer->storeBaseUrl;

        $priority   = $this->helper->getProductPriority($storeId);
        $changefreq = $this->helper->getProductChangefreq($storeId);

        /** @var \MageWorx\XmlSitemap\Model\ResourceModel\Catalog\Product $sitemapProductResource */
        $sitemapProductResource = $this->sitemapProductResourceFactory->create();
        $this->counter          = 0;

        while (!$sitemapProductResource->isCollectionReaded()) {
            $collection = $sitemapProductResource->getLimitedCollection(
                $storeId,
                self::COLLECTION_LIMIT,
                $this->usePubInMediaUrl
            );

            /** @var DataObject $product */
            foreach ($collection as $product) {

                /** @var DataObject $images */
                $images = $this->getIsAllowedImages() ? $product->getImages() : false;

                if ($images) {
                    //we don't add thumbnail URL here
                    $this->imageCounter += count($images->getCollection());
                }

                /** @var DataObject $videos */
                $videos = $this->getIsAllowedVideo() ? $product->getVideos() : false;

                if ($videos) {
                    //we don't add thumbnail URL here
                    $this->videoCounter += count($videos->getCollection());
                }

                $writer->write(
                    $this->getItemUrl($product),
                    $this->getItemChangeDate($product),
                    $changefreq,
                    $priority,
                    $images,
                    $videos
                );
            }
            $this->counter += count($collection);
            unset($collection);
        }
    }

    /**
     * @return bool
     */
    public function getIsAllowedImages()
    {
        return $this->helper->isProductImages(); //AIzaSyDml8OFxUAnA3R0lhpio3HarZUecqosIX0
    }

    /**
     * @return bool
     */
    public function getIsAllowedVideo()
    {
        return $this->helper->isProductVideos();
    }

    /**
     * @param Magento\Framework\DataObject $item
     * @return string
     */
    protected function getItemUrl($item)
    {
        if (strpos(trim($item->getUrl()), 'http') === 0) {
            $url = $item->getUrl();
        } else {
            $url = $this->storeBaseUrl . $item->getUrl();
        }

        return $this->helper->trailingSlash($url);
    }
}