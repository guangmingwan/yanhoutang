<?php
namespace Potato\Pdf\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Potato\Pdf\Model\Source\System\PrintMethod;
use Magento\Framework\Module\Dir\Reader as DirReader;

/**
 * Class Config
 */
class Config
{
    const GENERAL_ADDITIONAL_OPTION     = 'potato_pdf/general/additional_options';
    const GENERAL_LIB_PATH              = 'potato_pdf/general/lib_path';

    const GENERAL_IS_ENABLED            = 'potato_pdf/general/is_enabled';
    const GENERAL_PRINT_METHOD          = 'potato_pdf/general/print_method';

    const ADVANCED_PAGE_ORIENTATION      = 'potato_pdf/advanced/page_orientation';
    const ADVANCED_PAGE_FORMAT           = 'potato_pdf/advanced/page_format';
    const ADVANCED_MARGIN_TOP            = 'potato_pdf/advanced/margin_top';
    const ADVANCED_MARGIN_LEFT           = 'potato_pdf/advanced/margin_left';
    const ADVANCED_MARGIN_RIGHT          = 'potato_pdf/advanced/margin_right';
    const ADVANCED_MARGIN_BOTTOM         = 'potato_pdf/advanced/margin_bottom';

    const ORDER_ADMIN_TEMPLATE          = 'potato_pdf/order/admin_template';
    const ORDER_CUSTOMER_TEMPLATE       = 'potato_pdf/order/customer_template';

    const INVOICE_ADMIN_TEMPLATE        = 'potato_pdf/invoice/admin_template';
    const INVOICE_CUSTOMER_TEMPLATE     = 'potato_pdf/invoice/customer_template';

    const SHIPMENT_ADMIN_TEMPLATE       = 'potato_pdf/shipment/admin_template';
    const SHIPMENT_CUSTOMER_TEMPLATE    = 'potato_pdf/shipment/customer_template';

    const CREDIT_MEMO_ADMIN_TEMPLATE    = 'potato_pdf/creditmemo/admin_template';
    const CREDIT_MEMO_CUSTOMER_TEMPLATE = 'potato_pdf/creditmemo/customer_template';

    const ATTACH_PDF_TO_ORDER_EMAIL         = 'potato_pdf/email_settings/attach_to_order';
    const ATTACH_PDF_TO_INVOICE_EMAIL       = 'potato_pdf/email_settings/attach_to_invoice';
    const ATTACH_PDF_TO_SHIPMENT_EMAIL      = 'potato_pdf/email_settings/attach_to_shipment';
    const ATTACH_PDF_TO_CREDIT_MEMO_EMAIL   = 'potato_pdf/email_settings/attach_to_creditmemo';
    
    const PREVIEW_ORDER                 = 'potato_pdf/preview/order_increment_id';
    const PREVIEW_INVOICE               = 'potato_pdf/preview/invoice_increment_id';
    const PREVIEW_SHIPMENT              = 'potato_pdf/preview/shipment_increment_id';
    const PREVIEW_CREDITMEMO            = 'potato_pdf/preview/creditmemo_increment_id';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleDirReader;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DirReader $moduleDirReader
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->moduleDirReader = $moduleDirReader;
    }

    public function getAdditionalOptions($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::GENERAL_ADDITIONAL_OPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPreviewOrder($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::PREVIEW_ORDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPreviewInvoice($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::PREVIEW_INVOICE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPreviewShipment($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::PREVIEW_SHIPMENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPreviewCreditMemo($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::PREVIEW_CREDITMEMO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPageOrientation($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::ADVANCED_PAGE_ORIENTATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getLibPath($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_LIB_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPageFormat($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::ADVANCED_PAGE_FORMAT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMarginRight($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::ADVANCED_MARGIN_RIGHT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMarginLeft($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::ADVANCED_MARGIN_LEFT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMarginTop($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::ADVANCED_MARGIN_TOP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMarginBottom($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (int)$this->scopeConfig->getValue(
            self::ADVANCED_MARGIN_BOTTOM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isEnabled($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_IS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderAdminTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::ORDER_ADMIN_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderCustomerTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::ORDER_CUSTOMER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceAdminTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::INVOICE_ADMIN_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceCustomerTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::INVOICE_CUSTOMER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShipmentAdminTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::SHIPMENT_ADMIN_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShipmentCustomerTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::SHIPMENT_CUSTOMER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCreditMemoAdminTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::CREDIT_MEMO_ADMIN_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCreditMemoCustomerTemplate($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            self::CREDIT_MEMO_CUSTOMER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function canUseService($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $result = $this->scopeConfig->getValue(
            self::GENERAL_PRINT_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $result === PrintMethod::USE_SERVICE;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isAttachPdfToOrderEmail($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::ATTACH_PDF_TO_ORDER_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAttachPdfToInvoiceEmail($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::ATTACH_PDF_TO_INVOICE_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAttachPdfToShipmentEmail($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::ATTACH_PDF_TO_SHIPMENT_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAttachPdfToCreditMemoEmail($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return (bool)$this->scopeConfig->getValue(
            self::ATTACH_PDF_TO_CREDIT_MEMO_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $xpath
     * @return bool|\SimpleXMLElement[]
     */
    private function getCustomSettingXml($xpath)
    {
        $customFilePath = $this->moduleDirReader->getModuleDir('etc', 'Potato_Pdf')
            . DIRECTORY_SEPARATOR . 'custom_setting.xml';
        if (false === is_readable($customFilePath)) {
            return false;
        }
        $xmlSettings = simplexml_load_file($customFilePath);
        $result = [];
        if (false !== $xmlSettings) {
            $result = $xmlSettings->xpath($xpath);
        }
        return array_shift($result);
    }
}
