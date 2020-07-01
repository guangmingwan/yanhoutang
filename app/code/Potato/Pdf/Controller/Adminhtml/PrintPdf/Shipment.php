<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Shipment\PrintAction;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Potato\Pdf\Model\Manager\Pdf\Shipment as ShipmentPdfManager;

/**
 * Class Shipment
 */
class Shipment extends PrintAction
{
    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var Config
     */
    protected $config;

    /** @var ShipmentPdfManager  */
    protected $shipmentPdfManager;

    /**
     * Shipment constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param ForwardFactory $forwardFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Config $config
     * @param ShipmentPdfManager $shipmentPdfManager
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        ForwardFactory $forwardFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        Config $config,
        ShipmentPdfManager $shipmentPdfManager
    ) {
        parent::__construct($context, $fileFactory, $forwardFactory);
        $this->shipmentRepository = $shipmentRepository;
        $this->config = $config;
        $this->shipmentPdfManager = $shipmentPdfManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if (!$this->config->isEnabled($shipment->getStoreId())) {
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }

        $htmlContent = $this->shipmentPdfManager->getPdfHtmlContentByEntityId($shipment);
        try {
            $pdfContent = $this->shipmentPdfManager->convertHtmlToPdf([$htmlContent], $shipment->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }

        return $this->_fileFactory->create(
            'shipment-' . $shipment->getIncrementId() . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
