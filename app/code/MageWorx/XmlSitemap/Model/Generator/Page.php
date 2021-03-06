<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\DataObject;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\XmlSitemap\Model\Generator;
use MageWorx\XmlSitemap\Model\ResourceModel\Cms\PageFactory;
use MageWorx\SeoAll\Helper\Page as PageHelper;

/**
 * {@inheritdoc}
 */
class Page extends AbstractGenerator
{
    /**
     * @var
     */
    protected $cmsFactory;

    /**
     * @var PageHelper
     */
    protected $pageHelper;

    /**
     * Page constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param PageFactory $cmsFactory
     * @param PageHelper $pageHelper
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        PageFactory $cmsFactory,
        PageHelper $pageHelper
    ) {
        $this->code       = 'cms';
        $this->name       = __('CMS Pages');
        $this->cmsFactory = $cmsFactory;
        $this->pageHelper = $pageHelper;
        parent::__construct($helper, $objectManager);
    }

    /**
     * @param string $storeId
     * @param Generator $writer
     * @return mixed|void
     */
    public function generate($storeId, $writer)
    {
        $this->storeId = $storeId;
        $this->helper->init($this->storeId);
        $this->storeBaseUrl = $writer->storeBaseUrl;

        $changefreq    = $this->helper->getPageChangefreq($storeId);
        $collection    = $this->cmsFactory->create()->getCollection($storeId);
        $this->counter = count($collection);

        foreach ($collection as $item) {
            $isHomePage = $this->pageHelper->getIsHomePage($item->getUrl());

            if ($this->helper->isOptimizeHomePage() && $isHomePage) {
                $item->setUrl('');
                $priority = 1;
            } else {
                $priority = $this->helper->getPagePriority($storeId);
            }

            $writer->write(
                $this->getItemUrl($item, $isHomePage),
                $this->helper->getCurrentDate(),
                $changefreq,
                $priority,
                false
            );
        }
        unset($collection);
    }

    /**
     * @param DataObject $item
     * @param bool $isHomePage
     * @return string
     */
    protected function getItemUrl($item, $isHomePage)
    {
        if ($isHomePage) {
            return $this->helper->trailingSlash($this->storeBaseUrl, true);
        }

        return $this->helper->trailingSlash($this->storeBaseUrl . $item->getUrl());
    }
}