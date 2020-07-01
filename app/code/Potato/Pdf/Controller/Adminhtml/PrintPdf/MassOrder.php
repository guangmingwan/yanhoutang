<?php

namespace Potato\Pdf\Controller\Adminhtml\PrintPdf;

use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Backend\App\Action\Context;
use Potato\Pdf\Controller\Adminhtml\PrintPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\Pdf\Model\Manager\Pdf\Order as OrderPdfManager;

/**
 * Class MassOrder
 */
class MassOrder extends AbstractMassAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /** @var OrderPdfManager  */
    protected $orderPdfManager;

    /**
     * MassOrder constructor.
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param OrderPdfManager $orderPdfManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        Config $config,
        CollectionFactory $collectionFactory,
        OrderPdfManager $orderPdfManager
    ) {
        parent::__construct($context, $filter);
        $this->fileFactory = $fileFactory;
        $this->config = $config;
        $this->orderPdfManager = $orderPdfManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param AbstractCollection $collection
     * @return $this|ResponseInterface
     */
    public function massAction(AbstractCollection $collection)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->config->isEnabled()) {
            $this->messageManager->addErrorMessage(__("PDF Customizer is not enabled"));
            return $resultRedirect->setRefererUrl();
        }
        $htmlListContent = $this->orderPdfManager->getPdfHtmlContentByCollection($collection);
        try {
            $pdfContent = $this->orderPdfManager->convertHtmlToPdf([$htmlListContent]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        $date = new \DateTime;
        return $this->fileFactory->create(
            'orders-export-' . $date->format('Y-m-d') . '.pdf',
            $pdfContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
