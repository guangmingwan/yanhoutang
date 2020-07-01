<?php

namespace Potato\Pdf\Controller\PrintPdf;

use Magento\Sales\Controller\Order\PrintAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Framework\View\Result\PageFactory;
use Potato\Pdf\Model\Manager\Pdf\Order as OrderPdfManager;

/**
 * Class Order
 */
class Order extends PrintAction
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
     * @param Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param OrderPdfManager $orderPdfManager
     */
    public function __construct(
        Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        FileFactory $fileFactory,
        Config $config,
        OrderPdfManager $orderPdfManager
    ) {
        parent::__construct($context, $orderLoader, $resultPageFactory);
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
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }

        $htmlContent = $this->orderPdfManager->getPdfHtmlContentByEntityId($order, false, false);
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