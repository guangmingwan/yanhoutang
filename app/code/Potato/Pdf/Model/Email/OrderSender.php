<?php
namespace Potato\Pdf\Model\Email;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Potato\Pdf\Model\Config;
use Potato\Pdf\Model\Manager\Pdf\Order as OrderPdfManager;
use Psr\Log\LoggerInterface;
use Potato\Pdf\Api\Data\AttachmentInterface;
use Potato\Pdf\Api\Data\AttachmentInterfaceFactory;

/**
 * Class OrderSender
 * @package Potato\Pdf\Model\Email
 */
class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /** @var Config  */
    protected $config;

    /** @var OrderPdfManager  */
    protected $orderPdfManager;

    /** @var AttachmentInterfaceFactory  */
    protected $attachmentFactory;

    /**
     * OrderSender constructor.
     * @param Template $templateContainer
     * @param OrderIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     * @param Config $config
     * @param OrderPdfManager $orderPdfManager
     * @param LoggerInterface $logger
     * @param AttachmentInterfaceFactory $attachmentFactory
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Config $config,
        OrderPdfManager $orderPdfManager,
        AttachmentInterfaceFactory $attachmentFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, 
            $paymentHelper, $orderResource, $globalConfig, $eventManager);
        $this->config = $config;
        $this->orderPdfManager = $orderPdfManager;
        $this->attachmentFactory = $attachmentFactory;
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function prepareTemplate(Order $order)
    {
        parent::prepareTemplate($order);
        $this->addPoPdfAttachToVars();
    }

    /**
     * @return $this
     */
    protected function addPoPdfAttachToVars()
    {
        $templateVars = $this->templateContainer->getTemplateVars();
        if (!isset($templateVars['order'])) {
            return $this;
        }
        
        /** @var \Magento\Sales\Model\Order $order */
        $order = $templateVars['order'];
        if (!$this->config->isAttachPdfToOrderEmail($order->getStoreId())) {
            return $this;
        }
        $htmlContent = $this->orderPdfManager->getPdfHtmlContentByEntityId($order, false, true);
        try {
            $pdfContent = $this->orderPdfManager->convertHtmlToPdf([$htmlContent], $order->getStoreId());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $this;
        }
        $attachmentData = [
            AttachmentInterface::FILENAME => 'order-' . $order->getIncrementId() . '.pdf',
            AttachmentInterface::CONTENT => $pdfContent,
            AttachmentInterface::TYPE => 'application/pdf'
        ];
        $attachment = $this->attachmentFactory->create(['data' => $attachmentData]);
        $templateVars['po_pdf_attachment'] = $attachment;
        $this->templateContainer->setTemplateVars($templateVars);
        
        return $this;
    }
}
