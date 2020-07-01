<?php

namespace Potato\Pdf\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Framework\UrlInterface;
use Potato\Pdf\Model\Config;

/**
 * Class AddPrintButton
 */
class AddPrintButton
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * AddPrintButton constructor.
     * @param Config $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param View $view
     */
    public function beforeSetLayout(View $view)
    {
        $storeId = $view->getOrder()->getStoreId();
        if ($this->config->isEnabled($storeId) &&
            $this->config->getOrderAdminTemplate($storeId)
        ) {
            $view->addButton('potato_pdf',
                [
                    'label'   => __('Print'),
                    'class'   => 'save',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl($view->getOrder()->getId()) . '\')',
                ]
            );
        }
    }

    /**
     * @param int $orderId
     * @return string
     */
    private function getPrintUrl($orderId)
    {
        return $this->urlBuilder->getUrl('po_pdf/printPdf/order', ['order_id' => $orderId]);
    }
}