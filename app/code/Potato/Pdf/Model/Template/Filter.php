<?php
namespace Potato\Pdf\Model\Template;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Core Email Template Filter Model
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Filter extends \Magento\Email\Model\Template\Filter
{
    const CONSTRUCTION_FOREACH_PATTERN = '/{{foreach\s*(.*?)}}(.*?){{\\/foreach\s*}}/si';
    const CONSTRUCTION_HEADER_PATTERN = '/{{header\s*(.*?)}}(.*?){{\\/header\s*}}/si';
    const CONSTRUCTION_FOOTER_PATTERN = '/{{footer\s*(.*?)}}(.*?){{\\/footer\s*}}/si';
    const PRODUCT_SMALL_IMG_SIZE = 135;
    const PRODUCT_SMALL_THUMBNAIL_SIZE = 75;

    protected $allowedInstances = [
        'Magento\Sales\Model\Order\Invoice\Item',
        'Magento\Sales\Model\Order\Shipment\Item',
        'Magento\Sales\Model\Order\Creditmemo\Item',
        'Magento\Sales\Model\Order\Item',
    ];

    /** @var ProductRepositoryInterface  */
    protected $productRepository;

    /** @var Registry  */
    protected $registry;

    protected $pricingHelper;

    protected $productImageHelper;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @return mixed
     */
    protected function getPricingHelper()
    {
        if (!$this->pricingHelper) {
            $this->pricingHelper = ObjectManager::getInstance()
                ->get(\Magento\Framework\Pricing\Helper\Data::class);
        }
        return $this->pricingHelper;
    }

    /**
     * @return mixed
     */
    protected function getProductImageHelper()
    {
        if (!$this->productImageHelper) {
            $this->productImageHelper = ObjectManager::getInstance()
                ->get(\Magento\Catalog\Helper\Image::class);
        }
        return $this->productImageHelper;
    }

    /**
     * @return ProductRepositoryInterface|mixed
     */
    protected function getProductRepository()
    {
        if (!$this->productRepository) {
            $this->productRepository = ObjectManager::getInstance()
                ->get(ProductRepositoryInterface::class);
        }
        return $this->productRepository;
    }

    /**
     * @return TimezoneInterface|mixed
     */
    protected function getLocalDate()
    {
        if (!$this->localeDate) {
            $this->localeDate = ObjectManager::getInstance()
                ->get(TimezoneInterface::class);
        }
        return $this->localeDate;
    }

    /**
     * @return Registry|mixed
     */
    protected function getRegistry()
    {
        if (!$this->registry) {
            $this->registry = ObjectManager::getInstance()
                ->get(Registry::class);
        }
        return $this->registry;
    }

    /**
     * @param mixed $value
     * @return int
     */
    public function modifierInt($value)
    {
        return (int)$value;
    }

    /**
     * Get image url from product
     * @param string $value
     * @return string
     */
    public function modifierImage($value)
    {
        $productId = null;
        foreach ($this->allowedInstances as $instance) {
            if ($value instanceof $instance) {
                $productId = $value->getProductId();
                break;
            }
        }
        if (null === $productId) {
            return '';
        }
        try {
            $product = $this->getProductRepository()->getById($productId);
        } catch (NoSuchEntityException $e) {
            return '';
        }
        $resizedImage = $this->getProductImageHelper()
            ->init($product, 'category_page_grid', ['type' => 'thumbnail'])
            ->constrainOnly(TRUE)
            ->keepAspectRatio(TRUE)
            ->keepTransparency(TRUE)
            ->keepFrame(FALSE)
            ->resize(self::PRODUCT_SMALL_THUMBNAIL_SIZE);
        return $resizedImage->getUrl();
    }

    /**
     * Get small image url from product
     * @param string $value
     * @return string
     */
    public function modifierSmallImage($value)
    {
        $productId = null;
        foreach ($this->allowedInstances as $instance) {
            if ($value instanceof $instance) {
                $productId = $value->getProductId();
                break;
            }
        }
        if (null === $productId) {
            return '';
        }
        try {
            $product = $this->getProductRepository()->getById($productId);
        } catch (NoSuchEntityException $e) {
            return '';
        }
        $resizedImage = $this->getProductImageHelper()
            ->init($product, 'category_page_grid', ['type' => 'small_image'])
            ->constrainOnly(TRUE)
            ->keepAspectRatio(TRUE)
            ->keepTransparency(TRUE)
            ->keepFrame(FALSE)
            ->resize(self::PRODUCT_SMALL_IMG_SIZE);
        return $resizedImage->getUrl();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function modifierCurrency($value)
    {
        return $this->getPricingHelper()->currency($value, true, false);
    }
    
    /**
     * @param array $construction
     *
     * @return string
     */
    public function foreachDirective($construction)
    {
        if (!is_array($construction) || !array_key_exists(2, $construction) || empty($construction[2])) {
            return '';
        }
        $container = $this->getVariable($construction[1]);
        if (!is_array($container) &&
            !$container instanceof \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
        ) {
            return '';
        }
        //store "item" variable if exists
        $itemVal = null;
        if (array_key_exists('item', $this->templateVars)) {
            $itemVal = $this->templateVars['item'];
        }
        $result = '';
        foreach ($container as $item) {
            $this->templateVars['item'] = $item;
            $result .= $this->filter($construction[2]);
        }
        $this->templateVars['item'] = $itemVal;
        if (null === $itemVal) {
            unset($this->templateVars['item']);
        }
        return $result;
    }

    public function headerDirective($construction)
    {
        if (!is_array($construction)) {
            return '';
        }
        $header = array(
            'content' => $this->filter($construction[2]),
            'height'  => $construction[1]
        );
        $this->getRegistry()->register('header', $header, true);
        return '';
    }

    public function footerDirective($construction)
    {
        if (!is_array($construction)) {
            return '';
        }
        $footer = array(
            'content' => $this->filter($construction[2]),
            'height'  => $construction[1]
        );
        $this->getRegistry()->register('footer', $footer, true);
        return '';
    }

    /**
     * @param string $value
     * @param string $modifiers
     * @return string
     */
    protected function applyModifiers($value, $modifiers)
    {
        $this->_modifiers['int'] = [$this, 'modifierInt'];
        $this->_modifiers['thumbnail'] = [$this, 'modifierImage'];
        $this->_modifiers['small_image'] = [$this, 'modifierSmallImage'];
        $this->_modifiers['currency'] = array($this, 'modifierCurrency');
        $this->_modifiers['date'] = array($this, 'modifierDate');
        return parent::applyModifiers($value, $modifiers);
    }

    public function modifierDate($mysqlDate, $format)
    {
        return $this->getCreatedAtFormatted($format, $mysqlDate);
    }

    /**
     * @param   string $format date format type (short|medium|long|full)
     * @param   string $date
     * @return  string
     */
    public function getCreatedAtFormatted($format, $date)
    {
        $timezone = new \DateTimeZone($this->getLocalDate()->getConfigTimezone());
        $utc = new \DateTimeZone('UTC');

        $date = new \DateTime($date, $utc);
        $date->setTimezone($timezone);
        return $date->format($format);
    }
    
    /**
     * Filter the string as template.
     * Rewrited for logging exceptions
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        try {
            $value = $this->_filter($value);
        } catch (\Exception $e) {
            // Since a single instance of this class can be used to filter content multiple times, reset callbacks to
            // prevent callbacks running for unrelated content (e.g., email subject and email body)
            $this->resetAfterFilterCallbacks();

            if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $value = sprintf(__('Error filtering template: %s'), $e->getMessage());
            } else {
                $value = __("We're sorry, an error has occurred while generating this email.");
            }
            $this->_logger->critical($e);
        }
        return $value;
    }

    /**
     * Override parent method and check is empty variable 
     * @param string[] $construction
     * @return string
     */
    public function dependDirective($construction)
    {
        if (count($this->templateVars) == 0) {
            // If template processing
            return $construction[0];
        }
        list($directive, $modifiers) = $this->explodeModifiers($construction[1], 'escape');
        if (empty($this->applyModifiers($this->getVariable($directive, ''), $modifiers))) {
            return '';
        } else {
            return $construction[2];
        }
    }
    
    /**
     * @param string $value
     * @return mixed|string
     * @throws \Exception
     */
    public function _filter($value)
    {
        // "depend", "if", and "template" directives should be first
        foreach ([
                     self::CONSTRUCTION_FOREACH_PATTERN => 'foreachDirective',
                     self::CONSTRUCTION_DEPEND_PATTERN => 'dependDirective',
                     self::CONSTRUCTION_IF_PATTERN => 'ifDirective',
                     self::CONSTRUCTION_TEMPLATE_PATTERN => 'templateDirective',
                    self::CONSTRUCTION_HEADER_PATTERN => 'headerDirective',
                     self::CONSTRUCTION_FOOTER_PATTERN => 'footerDirective',
                 ] as $pattern => $directive) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $callback = [$this, $directive];
                    if (!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        if (preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $callback = [$this, $construction[1] . 'Directive'];
                if (!is_callable($callback)) {
                    continue;
                }
                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (\Exception $e) {
                    throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        $value = $this->afterFilter($value);
        return $value;
    }
}