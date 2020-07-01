<?php
namespace Potato\Pdf\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Creditmemo as CreditmemoModel;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magento\Sales\Model\Order\Shipment as ShipmentModel;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\GiftMessage\Helper\Message as GiftMessage;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Variables
 */
class Variables
{
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GiftMessage
     */
    protected $giftMessage;

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

    protected $customerRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Variables constructor.
     * @param TimezoneInterface $localeDate
     * @param PaymentHelper $paymentHelper
     * @param LoggerInterface $logger
     * @param Renderer $renderer
     * @param ScopeConfigInterface $scopeConfig
     * @param GiftMessage $giftMessage
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $memoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        TimezoneInterface $localeDate,
        PaymentHelper $paymentHelper,
        LoggerInterface $logger,
        Renderer $renderer,
        ScopeConfigInterface $scopeConfig,
        GiftMessage $giftMessage,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $shipmentCollectionFactory
    ) {
        $this->localeDate = $localeDate;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
        $this->addressRenderer = $renderer;
        $this->scopeConfig = $scopeConfig;
        $this->giftMessage = $giftMessage;
        $this->objectManager = $objectManager;
        $this->customerRepository = $customerRepository;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory->create();
        $this->memoCollectionFactory = $memoCollectionFactory->create();
        $this->shipmentCollectionFactory = $shipmentCollectionFactory->create();
    }

    /**
     * @param OrderModel $order
     * @return array
     */
    public function getOrderVariables(OrderModel $order)
    {
        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (\Exception $e) {
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
        }
        return [
            'store_id' => $order->getStoreId(),
            'order' => $order,
            'payment_html' => $this->getPaymentInfoFromOrder($order),
            'customer' => $customer,
            'cc_last_4' => $order->getPayment()->decrypt($order->getPayment()->getCcLast4()),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'giftMessage' => $this->getGiftMessage($order),
            'order_comments' => $order->getStatusHistoryCollection(true),
            'order_items' => $this->getAllVisibleItems($order),
            'createdAtFormatted' => $this->localeDate->date($order->getCreatedAt())->format('Y-m-d'),
            'order_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A', $order->getCreatedAt())
        ];
    }

    /**
     * @param InvoiceModel $invoice
     * @return array
     */
    public function getInvoiceVariables(InvoiceModel $invoice)
    {
        $order = $invoice->getOrder();
        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (\Exception $e) {
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
        }
        return [
            'store_id' => $order->getStoreId(),
            'invoice' => $invoice,
            'customer' => $customer,
            'order' => $order,
            'cc_last_4' => $order->getPayment()->decrypt($order->getPayment()->getCcLast4()),
            'invoice_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A',
                $invoice->getCreatedAt()),
            'payment_html' => $this->getPaymentInfoFromOrder($order),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'giftMessage' => $this->getGiftMessage($order),
            'order_comments' => $order->getStatusHistoryCollection(true),
            'invoice_items' => $this->getAllVisibleItems($invoice),
            'invoice_comments'  => $this->invoiceCollectionFactory->setParentFilter($invoice)->setCreatedAtOrder()->addVisibleOnFrontFilter(),
            'createdAtFormatted' => $this->localeDate->date($invoice->getCreatedAt())->format('Y-m-d'),
            'order_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A', $order->getCreatedAt())
        ];
    }

    /**
     * @param ShipmentModel $shipment
     * @return array
     */
    public function getShipmentVariables(ShipmentModel $shipment)
    {
        $order = $shipment->getOrder();
        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (\Exception $e) {
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
        }
        return [
            'store_id' => $order->getStoreId(),
            'shipment' => $shipment,
            'order' => $order,
            'cc_last_4' => $order->getPayment()->decrypt($order->getPayment()->getCcLast4()),
            'customer' => $customer,
            'shipment_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A',
                $shipment->getCreatedAt()),
            'payment_html' => $this->getPaymentInfoFromOrder($order),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'order_comments' => $order->getStatusHistoryCollection(true),
            'shipment_items' => $this->getAllVisibleItems($shipment),
            'shipment_comments'  => $this->shipmentCollectionFactory->setParentFilter($shipment)->setCreatedAtOrder()->addVisibleOnFrontFilter(),
            'giftMessage' => $this->getGiftMessage($order),
            'createdAtFormatted' => $this->localeDate->date($shipment->getCreatedAt())->format('Y-m-d'),
            'order_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A', $order->getCreatedAt())
        ];
    }

    /**
     * @param CreditmemoModel $creditMemo
     * @return array
     */
    public function getCreditMemoVariables(CreditmemoModel $creditMemo)
    {
        $order = $creditMemo->getOrder();
        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (\Exception $e) {
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
        }
        return [
            'store_id' => $order->getStoreId(),
            'creditmemo' => $creditMemo,
            'order' => $order,
            'customer' => $customer,
            'cc_last_4' => $order->getPayment()->decrypt($order->getPayment()->getCcLast4()),
            'creditmemo_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A',
                $creditMemo->getCreatedAt()),
            'payment_html' => $this->getPaymentInfoFromOrder($order),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'order_comments' => $order->getStatusHistoryCollection(true),
            'creditmemo_items' => $this->getAllVisibleItems($creditMemo),
            'giftMessage' => $this->getGiftMessage($order),
            'creditmemo_comments'  => $this->memoCollectionFactory->setParentFilter($creditMemo)->setCreatedAtOrder()->addVisibleOnFrontFilter(),
            'createdAtFormatted'   => $this->localeDate->date($creditMemo->getCreatedAt())->format('Y-m-d'),
            'order_formatted_date' => $this->getCreatedAtFormatted('M j, Y, h:i:s A', $order->getCreatedAt())
        ];
    }

    /**
     * @param OrderModel $order
     * @return \Magento\GiftMessage\Model\Message|null
     */
    private function getGiftMessage(OrderModel $order) 
    {
        $giftMessage = null;
        if ($this->giftMessage->isMessagesAllowed('order', $order, $order->getStore()) &&
            null !== $order->getGiftMessageId()
        ) {
            $giftMessage = $this->giftMessage->getGiftMessage($order->getGiftMessageId());
        }
        return $giftMessage;
    }
    
    /**
     * @param   string $format date format type (short|medium|long|full)
     * @param   string $date
     * @return  string
     */
    public function getCreatedAtFormatted($format, $date)
    {
        $timezone = new \DateTimeZone($this->localeDate->getConfigTimezone());
        $utc = new \DateTimeZone('UTC');

        $date = new \DateTime($date, $utc);
        $date->setTimezone($timezone);
        return $date->format($format);
    }

    /**
     * @param OrderModel $order
     * @return null|string
     */
    public function getPaymentInfoFromOrder(OrderModel $order)
    {
        if (null === $order->getPayment()) {
            return '';
        }
        return $this->paymentHelper->getInfoBlockHtml($order->getPayment(), $order->getStoreId());
    }

    /**
     * @param OrderModel $order
     * @return string|null
     */
    public function getFormattedShippingAddress($order)
    {
        if (!$order->getShippingAddress()) {
            return '';
        }
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param OrderModel $order
     * @return string|null
     */
    public function getFormattedBillingAddress($order)
    {
        if (!$order->getBillingAddress()) {
            return '';
        }
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    protected function getCopyright($storeId)
    {
        return $this->scopeConfig->getValue(
            'design/footer/copyright',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAllVisibleItems($object)
    {
        if ($object instanceof OrderModel)
        {
            return $object->getAllVisibleItems();
        }
        $items = array();
        foreach ($object->getAllItems() as $item) {
            if (!$item->getOrderItem() || $item->getOrderItem()->getParentItem()) {
                continue;
            }
            array_push($items, $item);
        }
        return $items;
    }
}