<?php
namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;
use Potato\Pdf\Model\Manager\Pdf\Order as OrderPdfManager;
use Potato\Pdf\Model\Manager\Pdf\Invoice as InvoicePdfManager;
use Potato\Pdf\Model\Manager\Pdf\Shipment as ShipmentPdfManager;
use Potato\Pdf\Model\Manager\Pdf\Creditmemo as CreditmemoPdfManager;
use Potato\Pdf\Model\Config;
use Magento\Sales\Controller\Adminhtml\Order\Pdfdocs;

/**
 * Class MassOrderPdf
 */
class MassOrderPdf extends Pdfdocs
{
    /** @var OrderPdfManager  */
    protected $orderPdfManager;

    /** @var InvoicePdfManager  */
    protected $invoicePdfManager;

    /** @var CreditmemoPdfManager  */
    protected $creditmemoPdfManager;

    /** @var ShipmentPdfManager  */
    protected $shipmentPdfManager;
    
    /** @var Config  */
    protected $config;

    /**
     * MassOrderPdf constructor.
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param Invoice $pdfInvoice
     * @param Shipment $pdfShipment
     * @param Creditmemo $pdfCreditmemo
     * @param DateTime $dateTime
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param CreditmemoCollectionFactory $creditmemoCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderPdfManager $orderPdfManager
     * @param InvoicePdfManager $invoicePdfManager
     * @param CreditmemoPdfManager $creditmemoPdfManager
     * @param ShipmentPdfManager $shipmentPdfManager
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        Invoice $pdfInvoice,
        Shipment $pdfShipment,
        Creditmemo $pdfCreditmemo,
        DateTime $dateTime,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        CreditmemoCollectionFactory $creditmemoCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderPdfManager $orderPdfManager,
        InvoicePdfManager $invoicePdfManager,
        CreditmemoPdfManager $creditmemoPdfManager,
        ShipmentPdfManager $shipmentPdfManager,
        Config $config
    ) {
        parent::__construct($context, $filter, $fileFactory, $pdfInvoice, $pdfShipment, $pdfCreditmemo, $dateTime,
            $shipmentCollectionFactory, $invoiceCollectionFactory, $creditmemoCollectionFactory,
            $orderCollectionFactory);
        $this->config = $config;
        $this->shipmentPdfManager = $shipmentPdfManager;
        $this->orderPdfManager = $orderPdfManager;
        $this->creditmemoPdfManager = $creditmemoPdfManager;
        $this->invoicePdfManager = $invoicePdfManager;
    }

    /**
     * Print all documents for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function massAction(AbstractCollection $collection)
    {
        if (!$this->config->isEnabled()) {
            return parent::massAction($collection);
        }
        $orderIds = $collection->getAllIds();

        $shipments = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $invoices = $this->invoiceCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $creditmemos = $this->creditmemoCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

        $documents = [];
        if ($invoices->getSize()) {
            $documents = array_merge($documents, $this->invoicePdfManager->getPdfHtmlContentByCollection($invoices));
        }
        if ($shipments->getSize()) {
            $documents = array_merge($documents, $this->shipmentPdfManager->getPdfHtmlContentByCollection($shipments));
        }
        if ($creditmemos->getSize()) {
            $documents = array_merge($documents, $this->creditmemoPdfManager->getPdfHtmlContentByCollection($creditmemos));
        }

        if (empty($documents)) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }
        $pdfContent = $this->orderPdfManager->convertHtmlToPdf($documents);
        return $this->fileFactory->create(
            sprintf('sales-export-%s.pdf', $this->dateTime->date('Y-m-d')),
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
