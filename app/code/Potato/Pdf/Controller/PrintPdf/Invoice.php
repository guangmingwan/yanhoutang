<?php

namespace Potato\Pdf\Controller\PrintPdf;

use Magento\Sales\Controller\Order\PrintInvoice;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Magento\Framework\View\Result\PageFactory;
use Potato\Pdf\Model\Manager\Pdf\Invoice as InvoicePdfManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Invoice
 */
class Invoice extends PrintInvoice
{
    /** @var FileFactory  */
    protected $fileFactory;

    /** @var InvoiceRepositoryInterface  */
    protected $invoiceRepository;

    /** @var Config  */
    protected $config;

    /** @var InvoicePdfManager  */
    protected $invoicePdfManager;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /**
     * Invoice constructor.
     * @param Context $context
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param InvoicePdfManager $invoicePdfManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        OrderViewAuthorizationInterface $orderAuthorization,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        FileFactory $fileFactory,
        Config $config,
        InvoicePdfManager $invoicePdfManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $orderAuthorization, $registry, $resultPageFactory);
        $this->fileFactory = $fileFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->config = $config;
        $this->invoicePdfManager = $invoicePdfManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if (null == $invoiceId && null !== $orderId) {
            return $this->printPdfForOrder($orderId);
        }

        try {
            $invoice = $this->invoiceRepository->get($invoiceId);
            if (!$this->config->isEnabled($invoice->getStoreId())) {
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }

        $htmlContent = $this->invoicePdfManager->getPdfHtmlContentByEntityId($invoice, false, false);
        try {
            $pdfContent = $this->invoicePdfManager->convertHtmlToPdf([$htmlContent], $invoice->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        return $this->fileFactory->create(
            'invoice-' . $invoice->getIncrementId() . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    private function printPdfForOrder($orderId)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $htmlListContent = $this->invoicePdfManager->getPdfHtmlContentByCollection($order->getInvoiceCollection());
        try {
            $pdfContent = $this->invoicePdfManager->convertHtmlToPdf([$htmlListContent]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $date = new \DateTime;
        return $this->fileFactory->create(
            'invoices-export-' . $date->format('Y-m-d') . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}