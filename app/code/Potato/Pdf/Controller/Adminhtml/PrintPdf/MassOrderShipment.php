<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Order\Pdfshipments;
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
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Potato\Pdf\Model\Manager\Pdf\Shipment as ShipmentPdfManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class MassOrderShipment
 */
class MassOrderShipment extends Pdfshipments
{
    /** @var Config  */
    protected $config;

    /** @var ShipmentPdfManager  */
    protected $shipmentPdfManager;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /**
     * MassOrderShipment constructor.
     * @param Context $context
     * @param Filter $filter
     * @param Shipment $pdfShipment
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Config $config
     * @param ShipmentPdfManager $shipmentPdfManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Shipment $pdfShipment,
        DateTime $dateTime,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        Config $config,
        ShipmentPdfManager $shipmentPdfManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $filter, $orderCollectionFactory, $dateTime, $fileFactory, $pdfShipment, $collectionFactory);
        $this->config = $config;
        $this->shipmentPdfManager = $shipmentPdfManager;
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
            $shipmentCollection = $order->getShipmentsCollection();
            $htmlInvoiceListContent = $this->shipmentPdfManager->getPdfHtmlContentByCollection($shipmentCollection);
            $htmlListContent = array_merge($htmlListContent, $htmlInvoiceListContent);
        }
        if (empty($htmlListContent)) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $resultRedirect->setRefererUrl();
        }

        try {
            $pdfContent = $this->shipmentPdfManager->convertHtmlToPdf([$htmlListContent]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $date = new \DateTime;
        return $this->fileFactory->create(
            'shipments-export-' . $date->format('Y-m-d') . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
