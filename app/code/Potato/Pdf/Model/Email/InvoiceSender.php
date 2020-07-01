<?php

namespace Potato\Pdf\Model\Email;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Potato\Pdf\Model\Config;
use Potato\Pdf\Model\Manager\Pdf\Invoice as InvoiceOrderPdfManager;
use Psr\Log\LoggerInterface;
use Potato\Pdf\Api\Data\AttachmentInterface;
use Potato\Pdf\Api\Data\AttachmentInterfaceFactory;

class InvoiceSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    /** @var Config  */
    protected $config;

    /** @var InvoiceOrderPdfManager  */
    protected $invoicePdfManager;

    /** @var AttachmentInterfaceFactory  */
    protected $attachmentFactory;

    /**
     * @param Template $templateContainer
     * @param InvoiceIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param PaymentHelper $paymentHelper
     * @param InvoiceResource $invoiceResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        InvoiceIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        InvoiceResource $invoiceResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Config $config,
        InvoiceOrderPdfManager $invoicePdfManager,
        AttachmentInterfaceFactory $attachmentFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer,
            $paymentHelper, $invoiceResource, $globalConfig, $eventManager);
        $this->config = $config;
        $this->invoicePdfManager = $invoicePdfManager;
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
        if (!isset($templateVars['invoice'])) {
            return $this;
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $templateVars['invoice'];
        if (!$this->config->isAttachPdfToInvoiceEmail($invoice->getStoreId())) {
            return $this;
        }
        $htmlContent = $this->invoicePdfManager->getPdfHtmlContentByEntityId($invoice, false, true);
        try {
            $pdfContent = $this->invoicePdfManager->convertHtmlToPdf([$htmlContent], $invoice->getStoreId());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $this;
        }
        $attachmentData = [
            AttachmentInterface::FILENAME => 'invoice-' . $invoice->getIncrementId() . '.pdf',
            AttachmentInterface::CONTENT => $pdfContent,
            AttachmentInterface::TYPE => 'application/pdf'
        ];
        $attachment = $this->attachmentFactory->create(['data' => $attachmentData]);
        $templateVars['po_pdf_attachment'] = $attachment;
        $this->templateContainer->setTemplateVars($templateVars);
        return $this;
    }
}
