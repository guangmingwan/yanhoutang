<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email
 * @version   2.1.41
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Email\EmailDesigner\Variable\Liquid;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\AbstractVariable;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Helper\Stock;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Order;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Quote;

class CrossSell extends AbstractVariable
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var LayoutInterface
     */
    protected $layout;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var Order
     */
    private $orderVar;
    /**
     * @var Quote
     */
    private $quoteVar;

    /**
     * @var Stock
     */
    private $stockFilter;

    /**
     * CrossSell constructor.
     * @param Order $orderVar
     * @param Quote $quoteVar
     * @param ProductFactory $productFactory
     * @param Stock $stockFilter
     * @param ProductCollectionFactory $productCollectionFactory
     * @param LayoutInterface $layout
     */
    public function __construct(
        Order                    $orderVar,
        Quote                    $quoteVar,
        ProductFactory           $productFactory,
        Stock                    $stockFilter,
        ProductCollectionFactory $productCollectionFactory,
        LayoutInterface          $layout
    ) {
        parent::__construct();

        $this->productCollectionFactory = $productCollectionFactory;
        $this->layout                   = $layout;
        $this->productFactory           = $productFactory;
        $this->stockFilter              = $stockFilter;
        $this->orderVar                 = $orderVar;
        $this->quoteVar                 = $quoteVar;
    }

    /**
     * Get block with cross sell products (depends on selected source)
     *
     * @return string
     */
    public function getCrossSellHtml()
    {
        $collection = $this->getCollection();

        /** @var \Mirasvit\Email\Block\CrossSell $crossBlock */
        $crossBlock = $this->layout->createBlock('Mirasvit\Email\Block\CrossSell');

        $crossBlock->setCollection($collection);

        return $crossBlock->toHtml();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getCollection()
    {
        $productIds = $this->getProductIds();
        $productIds[] = 0;

        $collection = $this->productCollectionFactory->create()
            ->setStoreId($this->context->getData('store_id'))
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('name')
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();

        $this->stockFilter->addIsInStockFilterToCollection($collection);

        $collection->getSelect()->reset('order');

        return $collection;
    }

    /**
     * @return array
     */
    protected function getProductIds()
    {
        if ($this->context->getData('preview')) {
            $collection = $this->productCollectionFactory->create();
            $collection->getSelect()
                ->orderRand()
                ->limit(20);

            return $collection->getAllIds(20);
        } else {
            $result = [];
            if ($this->context->getData('queue') && ($chain = $this->context->getData('queue')->getChain())) {
                /** @var ChainInterface $chain */
                if ($chain->getCrossSellsEnabled()) {
                    $baseProducts = $this->getBaseProducts();
                    foreach ($baseProducts as $baseProduct) {
                        if ($baseProduct instanceof \Magento\Catalog\Model\Product
                            && ($methodName = $chain->getCrossSellMethodName()) !== null
                        ) {
                            foreach ($baseProduct->$methodName() as $id) {
                                $result[] = $id;
                            }
                        }
                    }
                }
            }

            return $result;
        }
    }

    /**
     * @return array
     */
    protected function getBaseProducts()
    {
        $result = [];

        $this->orderVar->setContext($this->context);
        $this->quoteVar->setContext($this->context);

        if ($this->orderVar->getOrder()) {
            foreach ($this->orderVar->getOrder()->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($this->quoteVar->getQuote() && count($result) == 0) {
            foreach ($this->quoteVar->getQuote()->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($this->context->getData('product_id') && count($result) == 0) {
            $result[] = $this->productFactory->create()->load($this->context->getData('product_id'));
        }

        return array_filter($result);
    }
}
