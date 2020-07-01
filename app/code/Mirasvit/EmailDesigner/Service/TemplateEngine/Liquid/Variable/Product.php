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
 * @package   mirasvit/module-email-designer
 * @version   1.1.42
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\Core\Api\ImageHelperInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Store;

class Product extends AbstractVariable
{
    /**
     * @var array
     */
    protected $supportedTypes = [
        'Magento\Catalog\Model\Product'
    ];

    /**
     * @var array
     */
    protected $whitelist = [
        'getName',
        'getPrice',
        'getProductUrl',
    ];

    /**
     * @var ImageHelperInterface
     */
    private $imageHelper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Store
     */
    private $storeManager;

    /**
     * Product constructor.
     * @param ProductFactory       $productFactory
     * @param ImageHelperInterface $imageHelper
     * @param Store                $storeManager
     */
    public function __construct(
        ProductFactory       $productFactory,
        ImageHelperInterface $imageHelper,
        Store                $storeManager
    ) {
        parent::__construct();

        $this->productFactory = $productFactory;
        $this->imageHelper    = $imageHelper;
        $this->storeManager   = $storeManager;
    }

    /**
     * Get product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->context->getData('product');
        if ($product) {
            $product = $this->getProductByStore($product->getId());

            return $product;
        }

        if (!$product && ($item = $this->context->getData('item'))) {
            $product = $this->getProductByStore($item->getProduct()->getId());
        }

        if (!$product && ($productId = $this->context->getData('product_id'))) {
            $product = $this->getProductByStore($productId);
        }

        if ($product) {
            $this->context->setData('product', $product);
        }

        return $product;
    }

    /** VARIABLES **/

    /**
     * Get product image url
     *
     * @filter | resize: "image", 300
     *
     * @param ProductModel $product
     *
     * @return string
     */
    public function getImage(\Magento\Catalog\Model\Product $product = null)
    {
        $image   = '';
        if ($product || ($product = $this->getProduct())) {
            $product = $this->getProductByStore($product->getId());
            if (!$image = $product->getImage()) {
                $image = $product->getSmallImage();
            }
        }

        return $image;
    }

    /**
     * Get product url
     *
     * @return string
     */
    public function getProductUrl()
    {
        $result = '';
        $product = $this->getProduct();
        if ($product->getId()) {
            $result = $product->getProductUrl();
        }

        return $result;
    }

    /**
     * Get product object from specific store
     *
     * @param  int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductByStore($productId)
    {
        if ($productId) {
            $product = $this->productFactory->create()
                ->setStoreId($this->storeManager->getStore()->getId())
                ->load($productId);
        }

        return $product;
    }
}
