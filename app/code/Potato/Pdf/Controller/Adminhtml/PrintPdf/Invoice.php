<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction;
use Magento\Backend\App\Action\Context;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Potato\Pdf\Model\Manager\Pdf\Invoice as InvoicePdfManager;

/**
 * Class Invoice
 */
class Invoice extends PrintAction
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var Config
     */
    protected $config;

    /** @var InvoicePdfManager  */
    protected $invoicePdfManager;

    /**
     * Invoice constructor.
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ForwardFactory $forwardFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param Config $config
     * @param InvoicePdfManager $invoicePdfManager
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ForwardFactory $forwardFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        Config $config,
        InvoicePdfManager $invoicePdfManager
    ) {
        parent::__construct($context, $fileFactory, $forwardFactory);
        $this->invoiceRepository = $invoiceRepository;
        $this->config = $config;
        $this->invoicePdfManager = $invoicePdfManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);
            if (!$this->config->isEnabled($invoice->getStoreId())) {
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        
        $htmlContent = $this->invoicePdfManager->getPdfHtmlContentByEntityId($invoice);
        try {
            $pdfContent = $this->invoicePdfManager->convertHtmlToPdf([$htmlContent], $invoice->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        
        return $this->_fileFactory->create(
            'invoice-' . $invoice->getIncrementId() . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
