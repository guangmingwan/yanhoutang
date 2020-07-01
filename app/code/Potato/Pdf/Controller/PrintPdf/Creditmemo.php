<?php

namespace Potato\Pdf\Controller\PrintPdf;

use Magento\Sales\Controller\Order\PrintCreditmemo;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Magento\Framework\View\Result\PageFactory;
use Potato\Pdf\Model\Manager\Pdf\Creditmemo as CreditmemoPdfManager;

/**
 * Class Creditmemo
 */
class Creditmemo extends PrintCreditmemo
{
    /** @var FileFactory  */
    protected $fileFactory;

    /** @var CreditmemoRepositoryInterface  */
    protected $creditmemoRepository;

    /** @var Config  */
    protected $config;

    /** @var CreditmemoPdfManager  */
    protected $creditmemoPdfManager;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /**
     * Creditmemo constructor.
     * @param Context $context
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param CreditmemoPdfManager $creditmemoPdfManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        OrderViewAuthorizationInterface $orderAuthorization,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        FileFactory $fileFactory,
        Config $config,
        CreditmemoPdfManager $creditmemoPdfManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $orderAuthorization, $registry, $resultPageFactory, $creditmemoRepository);
        $this->fileFactory = $fileFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->config = $config;
        $this->creditmemoPdfManager = $creditmemoPdfManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if (null == $creditmemoId && null !== $orderId) {
            return $this->printPdfForOrder($orderId);
        }
        try {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if (!$this->config->isEnabled($creditmemo->getStoreId())) {
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $htmlContent = $this->creditmemoPdfManager->getPdfHtmlContentByEntityId($creditmemo, false, false);
        try {
            $pdfContent = $this->creditmemoPdfManager->convertHtmlToPdf([$htmlContent], $creditmemo->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        return $this->fileFactory->create(
            'credit-memo-' . $creditmemo->getIncrementId() . '.pdf',
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
        $htmlListContent = $this->creditmemoPdfManager->getPdfHtmlContentByCollection($order->getCreditmemosCollection());
        try {
            $pdfContent = $this->creditmemoPdfManager->convertHtmlToPdf([$htmlListContent]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $date = new \DateTime;
        return $this->fileFactory->create(
            'credit-memos-export-' . $date->format('Y-m-d') . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}