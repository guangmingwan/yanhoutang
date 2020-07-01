<?php
namespace Potato\Pdf\Model\Source\System;

use Magento\Config\Model\Config\Source\Email\Template as ConfigEmailTemplate;
use Magento\Framework\Registry;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as EmailCollectionFactory;
use Magento\Email\Model\Template\Config as TemplateConfig;
use Potato\Pdf\Api\TemplateRepositoryInterface;

/**
 * Class Template
 */
class Template extends ConfigEmailTemplate
{
    /** @var TemplateConfig  */
    private $_emailConfig;
    
    /** @var TemplateRepositoryInterface  */
    protected $templateRepository;

    /**
     * Template constructor.
     * @param Registry $coreRegistry
     * @param EmailCollectionFactory $templatesFactory
     * @param TemplateConfig $emailConfig
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        EmailCollectionFactory $templatesFactory,
        TemplateConfig $emailConfig,
        TemplateRepositoryInterface $templateRepository,
        array $data = []
    ) {
        parent::__construct($coreRegistry, $templatesFactory, $emailConfig, $data);
        $this->_emailConfig = $emailConfig;
        $this->templateRepository = $templateRepository;
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        /** @var \Potato\Pdf\Api\Data\TemplateInterface[] $templateList */
        $templateList = $this->templateRepository->getAllTemplates()->getItems();
        foreach ($templateList as $template) {
            $options[] = ['value' => $template->getId(), 'label' => $template->getTitle()];
        }
        
        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        return $options;
    }
}
