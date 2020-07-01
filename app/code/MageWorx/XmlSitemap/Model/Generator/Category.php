<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\XmlSitemap\Model\ResourceModel\Catalog\CategoryFactory;
use MageWorx\XmlSitemap\Model\WriterInterface;
use Zend_Db_Statement_Exception;

/**
 * {@inheritdoc}
 */
class Category extends AbstractMediaGenerator
{
    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Category constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        CategoryFactory $categoryFactory
    ) {
        $this->code            = 'category';
        $this->name            = __('Categories');
        $this->categoryFactory = $categoryFactory;
        parent::__construct($helper, $objectManager);
    }

    /**
     * @param int $storeId
     * @param WriterInterface $writer
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function generate($storeId, $writer)
    {
        $this->storeId = $storeId;
        $this->helper->init($this->storeId);
        $this->storeBaseUrl = $writer->storeBaseUrl;

        $changefreq = $this->helper->getCategoryChangefreq($storeId);
        $priority   = $this->helper->getCategoryPriority($storeId);

        $this->counter = 0;
        $categoryModel = $this->categoryFactory->create();

        while (!$categoryModel->isCollectionReaded()) {
            $collection = $categoryModel->getLimitedCollection(
                $storeId,
                self::COLLECTION_LIMIT,
                $this->usePubInMediaUrl
            );

            foreach ($collection as $item) {

                $images = $this->getIsAllowedImages() ? $item->getImages() : false;

                if ($images) {
                    $count = count($images->getCollection());

                    if ($count) {
                        $this->imageCounter += $count + 1;
                    }
                }

                $writer->write(
                    $this->getItemUrl($item),
                    $this->getItemChangeDate($item),
                    $changefreq,
                    $priority,
                    $images
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
        return $this->helper->isCategoryImages();
    }

    /**
     * @param DataObject $item
     * @return string
     */
    protected function getItemUrl($item)
    {
        $url = $this->storeBaseUrl . $item->getUrl();

        return $this->helper->trailingSlash($url);
    }
}