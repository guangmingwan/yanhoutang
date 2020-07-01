<?php

namespace Potato\Pdf\Model;

use Potato\Pdf\Api\Data;
use Magento\Framework;

/**
 * Class Template
 */
class Template extends Framework\Model\AbstractModel
{
    /**
     * @var \Potato\Pdf\Api\Data\TemplateInterfaceFactory
     */
    private $templateDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param \Potato\Pdf\Model\ResourceModel\Template $resource
     * @param \Potato\Pdf\Api\Data\TemplateInterfaceFactory $templateDataFactory
     * @param Framework\Api\DataObjectHelper $dataObjectHelper
     * @param Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Template $resource,
        ResourceModel\Template\Collection $resourceCollection,
        Data\TemplateInterfaceFactory $templateDataFactory,
        Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Template::class);
    }

    /**
     * Retrieve Template model with data
     *
     * @return \Potato\Pdf\Api\Data\TemplateInterface
     */
    public function getDataModel()
    {
        $data = $this->getData();
        $dataObject = $this->templateDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            Data\TemplateInterface::class
        );
        return $dataObject;
    }
}
