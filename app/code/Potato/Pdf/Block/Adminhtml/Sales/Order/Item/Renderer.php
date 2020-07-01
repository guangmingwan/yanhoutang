<?php

namespace Potato\Pdf\Block\Adminhtml\Sales\Order\Item;


class Renderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    protected $productHelper;
    protected $productRepository;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    public function __construct(
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->_assetRepo = $context->getAssetRepository();
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    public function getImageUrl()
    {
        if (!$product = $this->getProduct()) {
            return $this->_assetRepo->getUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
        }
        return $this->productHelper->getSmallImageUrl($product);
    }

    public function getName()
    {
        if (!$product = $this->getProduct()) {
            return $this->escapeHtml($this->getItem()->getName());
        }
        return $this->escapeHtml($product->getName());
    }

    protected function getProduct()
    {
        try {
            $product = $this->productRepository->get($this->getItem()->getSku());
            return $product;
        } catch (\Exception $e) { }
        try {
            $product = $this->productRepository->getById($this->getItem()->getProductId());
        } catch (\Exception $e) {
            return false;
        }
        return $product;
    }
}