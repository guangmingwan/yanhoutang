<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Order\Pdfinvoices;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\Pdf\Model\Manager\Pdf\Invoice as InvoicePdfManager;
use Magento\Sales\Api\OrderRepositoryInterface;


/**
 * Class MassOrderInvoice
 */
class MassOrderInvoice extends Pdfinvoices
{
    /** @var Config  */
    protected $config;

    /** @var InvoicePdfManager  */
    protected $invoicePdfManager;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /**
     * MassOrderInvoice constructor.
     * @param Context $context
     * @param Filter $filter
     * @param Invoice $pdfInvoice
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param InvoicePdfManager $invoicePdfManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Invoice $pdfInvoice,
        DateTime $dateTime,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        Config $config,
        InvoicePdfManager $invoicePdfManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory, $dateTime, $fileFactory, $pdfInvoice);
        $this->config = $config;
        $this->invoicePdfManager = $invoicePdfManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param AbstractCollection $collection
     * @return $this|ResponseInterface
     */
    public function massAction(AbstractCollection $collection)
    {
        if (!$this->config->isEnabled()) {
            return parent::massAction($collection);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $htmlListContent = [];
        foreach($collection as $item) {
            try {
                $order = $this->orderRepository->get($item->getEntityId());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                continue;
            }
            $invoiceCollection = $order->getInvoiceCollection();
            $htmlInvoiceListContent = $this->invoicePdfManager->getPdfHtmlContentByCollection($invoiceCollection);
            $htmlListContent = array_merge($htmlListContent, $htmlInvoiceListContent);
        }

        if (empty($htmlListContent)) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $resultRedirect->setRefererUrl();
        }

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
