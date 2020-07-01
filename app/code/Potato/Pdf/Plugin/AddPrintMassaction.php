<?php

namespace Potato\Pdf\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Framework\UrlInterface;
use Potato\Pdf\Model\Config;
use Magento\Ui\Component\ActionFactory;

/**
 * Class AddPrintMassaction
 */
class AddPrintMassaction
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
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * AddPrintMassaction constructor.
     * @param Config $config
     * @param UrlInterface $urlBuilder
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder,
        ActionFactory $actionFactory
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->actionFactory = $actionFactory;
    }

    /**
     * @param \Magento\Ui\Component\MassAction $subject
     */
    public function beforePrepare($subject)
    {
        if (
            $subject->getContext()->getNamespace() === 'sales_order_grid' &&
            $this->config->isEnabled() &&
            $this->config->getOrderAdminTemplate()
        ) {
            /** @var \Magento\Ui\Component\Action $action */
            $action = $this->actionFactory->create();
            $action->setData('config', [
                'component' => 'uiComponent',
                'type' => 'print_order',
                'label' => 'Print Order',
                'url' => $this->getPrintUrl()
            ]);
            $subject->addComponent('print_order', $action);
        }
    }

    /**
     * @return string
     */
    private function getPrintUrl()
    {
        return $this->urlBuilder->getUrl('po_pdf/printPdf/massOrder');
    }
}