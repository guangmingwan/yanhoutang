<?php

namespace Potato\Pdf\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Template
 */
class TemplateRegistry
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var array
     */
    private $templateRegistryById = [];

    /**
     * @var ResourceModel\Template
     */
    private $templateResource;

    /**
     * @param TemplateFactory $templateFactory
     * @param ResourceModel\Template $templateResource
     */
    public function __construct(
        TemplateFactory $templateFactory,
        ResourceModel\Template $templateResource
    ) {
        $this->templateResource = $templateResource;
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param int $templateId
     * @return Template
     * @throws NoSuchEntityException
     */
    public function retrieve($templateId)
    {
        if (!isset($this->templateRegistryById[$templateId])) {
            /** @var Template $template */
            $template = $this->templateFactory->create();
            $this->templateResource->load($template, $templateId);
            if (!$template->getId()) {
                throw NoSuchEntityException::singleField('templateId', $templateId);
            } else {
                $this->templateRegistryById[$templateId] = $template;
            }
        }
        return $this->templateRegistryById[$templateId];
    }

    /**
     * @param int $templateId
     * @return void
     */
    public function remove($templateId)
    {
        if (isset($this->templateRegistryById[$templateId])) {
            unset($this->templateRegistryById[$templateId]);
        }
    }

    /**
     * @param Template $template
     * @return $this
     */
    public function push(Template $template)
    {
        $this->templateRegistryById[$template->getId()] = $template;
        return $this;
    }
}
