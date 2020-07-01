<?php

namespace Potato\Pdf\Block\Adminhtml\Template\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class DeleteButton
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getTemplateId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->context->getUrlBuilder()->getUrl(
            '*/*/delete',
            ['id' => $this->context->getRequest()->getParam('id')]
        );
    }

    /**
     * @return int|null
     */
    public function getTemplateId()
    {
        return $this->context->getRequest()->getParam('id', null);
    }
}
