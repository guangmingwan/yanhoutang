<?php

namespace Potato\Pdf\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\Template;
use Magento\Framework\Controller\Result;
use Potato\Pdf\Api\TemplateRepositoryInterface;
use Potato\Pdf\Model\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Potato\Pdf\Model\Manager\Template as TemplateManager;

/**
 * Class NewAction
 */
class Preview extends Template
{
    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    protected $resultRawFactory;

    /** @var Config  */
    protected $config;

    protected $searchCriteriaBuilder;

    protected $invoiceRepository;

    protected $orderRepository;

    protected $shipmentRepository;

    protected $creditmemoRepository;

    protected $customerRepository;

    protected $variables;

    protected $templateManager;

    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory
     */
    protected $memoCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * Preview constructor.
     * @param Action\Context $context
     * @param TemplateRepositoryInterface $templateRepository
     * @param Result\RawFactory $resultRawFactory
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TemplateManager $templateManager
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $memoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        TemplateRepositoryInterface $templateRepository,
        Result\RawFactory $resultRawFactory,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TemplateManager $templateManager,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct($context, $templateRepository);
        $this->resultRawFactory = $resultRawFactory;
        $this->templateRepository = $templateRepository;
        $this->config = $config;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->customerRepository = $customerRepository;
        $this->variables = $this->_objectManager->create('Potato\Pdf\Model\Variables');
        $this->templateManager = $templateManager;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory->create();
        $this->memoCollectionFactory = $memoCollectionFactory->create();
        $this->shipmentCollectionFactory = $shipmentCollectionFactory->create();
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pdfContent = $this->getPdfContent();
        $resultPage = $this->resultRawFactory->create();
        $resultPage
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/pdf', true)
            ->setHeader('Content-Length', strlen($pdfContent), true)
            ->setHeader('Last-Modified', date('r'), true)
            ->setContents($pdfContent)
        ;
        return $resultPage;
    }

    protected function loadByIncrement($repository, $increment)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $increment, 'gteq')
            ->setPageSize(1)
            ->create()
        ;
        $items = $repository->getList($searchCriteria)->getItems();
        if(!empty($items)) {
            return $items[key($items)];
        }
        return null;
    }

    protected function getPdfContent()
    {
        //prepare preview invoice
        $invoiceId = $this->config->getPreviewInvoice();
        if (!$invoice = $this->loadByIncrement($this->invoiceRepository, $invoiceId)) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice');
        }

        //prepare preview order
        $orderId = $this->config->getPreviewOrder();
        if (!$order = $this->loadByIncrement($this->orderRepository, $orderId)) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        }

        //prepare preview shipment
        $shipmentId = $this->config->getPreviewShipment();
        if (!$shipment = $this->loadByIncrement($this->shipmentRepository, $shipmentId)) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment');
        }

        //prepare preview credit memo
        $creditmemoId = $this->config->getPreviewCreditMemo();
        if (!$creditMemo = $this->loadByIncrement($this->creditmemoRepository, $creditmemoId)) {
            $creditMemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo');
        }

        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (\Exception $e) {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
        }
        //prepare variables
        $variables = array(
            'store_id'                  => $order->getStoreId(),
            'invoice'                   => $invoice,
            'order_formatted_date'      => $this->variables->getCreatedAtFormatted('M j, Y, h:i:s A',
                $order->getCreatedAt()),
            'invoice_formatted_date'    => $this->variables->getCreatedAtFormatted('M j, Y, h:i:s A',
                $invoice->getCreatedAt()),
            'shipment_formatted_date'   => $this->variables->getCreatedAtFormatted('M j, Y, h:i:s A',
                $shipment->getCreatedAt()),
            'creditmemo_formatted_date' => $this->variables->getCreatedAtFormatted('M j, Y, h:i:s A',
                $creditMemo->getCreatedAt()),
            'order'                     => $order,
            'formattedShippingAddress'  => $this->variables->getFormattedShippingAddress($order),
            'formattedBillingAddress'   => $this->variables->getFormattedBillingAddress($order),
            'order_items'               => $this->variables->getAllVisibleItems($order),
            'invoice_items'             => $this->variables->getAllVisibleItems($invoice),
            'creditmemo_items'          => $this->variables->getAllVisibleItems($creditMemo),
            'shipment_items'            => $this->variables->getAllVisibleItems($shipment),
            'customer'                  => $customer,
            'shipment'                  => $shipment,
            'order_comments'            => $order->getStatusHistoryCollection(true),
            'invoice_comments'          => $this->invoiceCollectionFactory->setParentFilter($invoice)->setCreatedAtOrder()->addVisibleOnFrontFilter(),
            'shipment_comments'         => $this->shipmentCollectionFactory->setParentFilter($shipment)->setCreatedAtOrder()->addVisibleOnFrontFilter(),
            'creditmemo_comments'       => $this->memoCollectionFactory->setParentFilter($creditMemo)->setCreatedAtOrder()->addVisibleOnFrontFilter(),
            'creditmemo'                => $creditMemo,
            'payment_html'              => $this->variables->getPaymentInfoFromOrder($order)
        );

        $templateRepository = $this->_objectManager->create('Potato\Pdf\Model\ResourceModel\TemplateRepository');
        $template = $templateRepository->create([]);
        $template->setContent($this->getRequest()->getParam('content', ''));
        $html = $this->templateManager->getTemplateHtml($template, $variables, $order->getStoreId());
        try {
            //convert
            $pdfContent = $this->templateManager->processPdf([$html], $order->getStoreId());
        } catch (\Exception $e) {
            $pdfContent = '';
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $pdfContent;
    }
}