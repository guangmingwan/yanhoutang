<?php

namespace Potato\Pdf\Model\Email;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Potato\Pdf\Model\Config;
use Potato\Pdf\Model\Manager\Pdf\Creditmemo as CreditmemoPdfManager;
use Psr\Log\LoggerInterface;
use Potato\Pdf\Api\Data\AttachmentInterface;
use Potato\Pdf\Api\Data\AttachmentInterfaceFactory;

/**
 * Class CreditmemoSender
 */
class CreditmemoSender extends \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
{
    /** @var Config  */
    protected $config;

    /** @var CreditmemoPdfManager  */
    protected $creditmemoPdfManager;

    /** @var AttachmentInterfaceFactory  */
    protected $attachmentFactory;

    /**
     * CreditmemoSender constructor.
     * @param Template $templateContainer
     * @param CreditmemoIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param CreditmemoResource $creditmemoResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     * @param Config $config
     * @param CreditmemoPdfManager $creditmemoPdfManager
     * @param AttachmentInterfaceFactory $attachmentFactory
     */
    public function __construct(
        Template $templateContainer,
        CreditmemoIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        CreditmemoResource $creditmemoResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Config $config,
        CreditmemoPdfManager $creditmemoPdfManager,
        AttachmentInterfaceFactory $attachmentFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer,
            $paymentHelper, $creditmemoResource, $globalConfig, $eventManager);
        $this->config = $config;
        $this->creditmemoPdfManager = $creditmemoPdfManager;
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
        if (!isset($templateVars['creditmemo'])) {
            return $this;
        }
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $templateVars['creditmemo'];
        if (!$this->config->isAttachPdfToCreditMemoEmail($creditmemo->getStoreId())) {
            return $this;
        }
        $htmlContent = $this->creditmemoPdfManager->getPdfHtmlContentByEntityId($creditmemo, false, true);
        try {
            $pdfContent = $this->creditmemoPdfManager->convertHtmlToPdf([$htmlContent], $creditmemo->getStoreId());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $this;
        }
        $attachmentData = [
            AttachmentInterface::FILENAME => 'credit-memo-' . $creditmemo->getIncrementId() . '.pdf',
            AttachmentInterface::CONTENT => $pdfContent,
            AttachmentInterface::TYPE => 'application/pdf'
        ];
        $attachment = $this->attachmentFactory->create(['data' => $attachmentData]);
        $templateVars['po_pdf_attachment'] = $attachment;
        $this->templateContainer->setTemplateVars($templateVars);
        return $this;
    }
}
