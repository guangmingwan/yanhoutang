<?php

namespace Potato\Pdf\Controller\PrintPdf;

use Magento\Sales\Controller\Order\PrintShipment;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Magento\Framework\View\Result\PageFactory;
use Potato\Pdf\Model\Manager\Pdf\Shipment as ShipmentPdfManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Shipment
 */
class Shipment extends PrintShipment
{
    /** @var FileFactory  */
    protected $fileFactory;

    /** @var ShipmentRepositoryInterface  */
    protected $shipmentRepository;

    /** @var Config  */
    protected $config;

    /** @var ShipmentPdfManager  */
    protected $shipmentPdfManager;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /**
     * Shipment constructor.
     * @param Context $context
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param ShipmentPdfManager $shipmentPdfManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        OrderViewAuthorizationInterface $orderAuthorization,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        FileFactory $fileFactory,
        Config $config,
        ShipmentPdfManager $shipmentPdfManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $orderAuthorization, $registry, $resultPageFactory);
        $this->fileFactory = $fileFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->config = $config;
        $this->shipmentPdfManager = $shipmentPdfManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if (null == $shipmentId && null !== $orderId) {
            return $this->printPdfForOrder($orderId);
        }
        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if (!$this->config->isEnabled($shipment->getStoreId())) {
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $htmlContent = $this->shipmentPdfManager->getPdfHtmlContentByEntityId($shipment, false, false);
        try {
            $pdfContent = $this->shipmentPdfManager->convertHtmlToPdf([$htmlContent], $shipment->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        return $this->fileFactory->create(
            'shipment-' . $shipment->getIncrementId() . '.pdf',
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
        $htmlListContent = $this->shipmentPdfManager->getPdfHtmlContentByCollection($order->getShipmentsCollection());
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