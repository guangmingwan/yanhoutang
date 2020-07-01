<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Potato\Pdf\Model\Manager\Pdf\Order as OrderPdfManager;

/**
 * Class Order
 */
class Order extends Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Config
     */
    protected $config;

    /** @var OrderPdfManager  */
    protected $orderPdfManager;

    /**
     * Order constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     * @param OrderPdfManager $orderPdfManager
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        OrderRepositoryInterface $orderRepository,
        Config $config,
        OrderPdfManager $orderPdfManager
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->orderPdfManager = $orderPdfManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $order = $this->orderRepository->get($orderId);
            if (!$this->config->isEnabled($order->getStoreId())) {
                $this->messageManager->addErrorMessage(__("PDF Customizer is not enabled"));
                return $resultRedirect->setRefererUrl();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }

        $htmlContent = $this->orderPdfManager->getPdfHtmlContentByEntityId($order);
        try {
            $pdfContent = $this->orderPdfManager->convertHtmlToPdf([$htmlContent], $order->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        
        return $this->fileFactory->create(
            'order-' . $order->getIncrementId() . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
