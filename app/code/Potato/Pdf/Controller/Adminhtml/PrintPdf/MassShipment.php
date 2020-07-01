<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Shipment\Pdfshipments;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\Pdf\Model\Manager\Pdf\Shipment as ShipmentPdfManager;

/**
 * Class MassShipment
 */
class MassShipment extends Pdfshipments
{
    /**
     * @var Config
     */
    protected $config;

    /** @var ShipmentPdfManager  */
    protected $shipmentPdfManager;

    /**
     * MassShipment constructor.
     * @param Context $context
     * @param Filter $filter
     * @param Shipment $pdfInvoice
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param ShipmentPdfManager $shipmentPdfManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Shipment $pdfInvoice,
        DateTime $dateTime,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        Config $config,
        ShipmentPdfManager $shipmentPdfManager
    ) {
        parent::__construct($context, $filter, $dateTime, $fileFactory, $pdfInvoice, $collectionFactory);
        $this->config = $config;
        $this->shipmentPdfManager = $shipmentPdfManager;
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
        $htmlListContent = $this->shipmentPdfManager->getPdfHtmlContentByCollection($collection);
        try {
            $pdfContent = $this->shipmentPdfManager->convertHtmlToPdf([$htmlListContent]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $date = new \DateTime;
        return $this->fileFactory->create(
            'shipments-export-' . $date->format('Y-m-d_H-i-s') . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
