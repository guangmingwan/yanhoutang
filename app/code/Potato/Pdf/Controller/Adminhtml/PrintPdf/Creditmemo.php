<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\PrintAction;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Potato\Pdf\Model\Manager\Pdf\Creditmemo as CreditmemoPdfManager;

/**
 * Class Creditmemo
 */
class Creditmemo extends PrintAction
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var Config
     */
    protected $config;

    /** @var CreditmemoPdfManager  */
    protected $creditmemoPdfManager;

    /**
     * Creditmemo constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param ForwardFactory $forwardFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param Config $config
     * @param CreditmemoPdfManager $creditmemoPdfManager
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        ForwardFactory $forwardFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        Config $config,
        CreditmemoPdfManager $creditmemoPdfManager
    ) {
        parent::__construct($context, $fileFactory, $forwardFactory, $creditmemoRepository);
        $this->creditmemoRepository = $creditmemoRepository;
        $this->config = $config;
        $this->creditmemoPdfManager = $creditmemoPdfManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if (!$this->config->isEnabled($creditmemo->getStoreId())) {
                return parent::execute();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }

        $htmlContent = $this->creditmemoPdfManager->getPdfHtmlContentByEntityId($creditmemo);
        try {
            $pdfContent = $this->creditmemoPdfManager->convertHtmlToPdf([$htmlContent], $creditmemo->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        return $this->_fileFactory->create(
            'credit-memo-' . $creditmemo->getIncrementId() . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
