<?php
namespace Potato\Pdf\Model\Email;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Potato\Pdf\Model\Config;
use Psr\Log\LoggerInterface;
use Potato\Pdf\Api\Data\AttachmentInterface;
use Potato\Pdf\Api\Data\AttachmentInterfaceFactory;
use Potato\Pdf\Model\Manager\Pdf\Shipment as ShipmentPdfManager;

/**
 * Class ShipmentSender
 */
class ShipmentSender extends \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
{
    /** @var Config  */
    protected $config;

    /** @var ShipmentPdfManager  */
    protected $shipmentPdfManager;

    /** @var AttachmentInterfaceFactory  */
    protected $attachmentFactory;

    /**
     * ShipmentSender constructor.
     * @param Template $templateContainer
     * @param ShipmentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param ShipmentResource $shipmentResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     * @param Config $config
     * @param ShipmentPdfManager $shipmentPdfManager
     * @param LoggerInterface $logger
     * @param AttachmentInterfaceFactory $attachmentFactory
     */
    public function __construct(
        Template $templateContainer,
        ShipmentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        ShipmentResource $shipmentResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Config $config,
        ShipmentPdfManager $shipmentPdfManager,
        AttachmentInterfaceFactory $attachmentFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer,
            $paymentHelper, $shipmentResource, $globalConfig, $eventManager);
        $this->config = $config;
        $this->shipmentPdfManager = $shipmentPdfManager;
        $this->attachmentFactory = $attachmentFactory;

    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function checkAndSend(Order $order)
    {
        $this->addPoPdfAttachToVars();
        return parent::checkAndSend($order);
    }

    /**
     * @return $this
     */
    protected function addPoPdfAttachToVars()
    {
        $templateVars = $this->templateContainer->getTemplateVars();
        if (!isset($templateVars['shipment'])) {
            return $this;
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $templateVars['shipment'];
        if (!$this->config->isAttachPdfToShipmentEmail($shipment->getStoreId())) {
            return $this;
        }
        $htmlContent = $this->shipmentPdfManager->getPdfHtmlContentByEntityId($shipment, false, true);
        try {
            $pdfContent = $this->shipmentPdfManager->convertHtmlToPdf([$htmlContent], $shipment->getStoreId());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $this;
        }
        $attachmentData = [
            AttachmentInterface::FILENAME => 'shipment-' . $shipment->getIncrementId() . '.pdf',
            AttachmentInterface::CONTENT => $pdfContent,
            AttachmentInterface::TYPE => 'application/pdf'
        ];
        $attachment = $this->attachmentFactory->create(['data' => $attachmentData]);
        $templateVars['po_pdf_attachment'] = $attachment;
        $this->templateContainer->setTemplateVars($templateVars);
        return $this;
    }

}
