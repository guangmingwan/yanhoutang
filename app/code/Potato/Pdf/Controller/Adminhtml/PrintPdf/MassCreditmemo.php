<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Creditmemo\Pdfcreditmemos;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\Pdf\Model\Manager\Pdf\Creditmemo as CreditmemoPdfManager;

/**
 * Class MassCreditmemo
 */
class MassCreditmemo extends Pdfcreditmemos
{
    /**
     * @var Config
     */
    protected $config;

    /** @var CreditmemoPdfManager  */
    protected $creditmemoPdfManager;

    /**
     * MassCreditmemo constructor.
     * @param Context $context
     * @param Filter $filter
     * @param Creditmemo $pdfCreditmemo
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param CreditmemoPdfManager $creditmemoPdfManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Creditmemo $pdfCreditmemo,
        DateTime $dateTime,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        Config $config,
        CreditmemoPdfManager $creditmemoPdfManager
    ) {
        parent::__construct($context, $filter, $pdfCreditmemo, $dateTime, $fileFactory, $collectionFactory);
        $this->config = $config;
        $this->creditmemoPdfManager = $creditmemoPdfManager;
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
        $htmlListContent = $this->creditmemoPdfManager->getPdfHtmlContentByCollection($collection);
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
