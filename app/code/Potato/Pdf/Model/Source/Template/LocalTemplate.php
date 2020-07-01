<?php

namespace Potato\Pdf\Model\Source\Template;

use Potato\Pdf\Model\ResourceModel\Template\Collection as TemplateCollection;
use Magento\Email\Model\Template\Config as TemplateConfig;
use Magento\Email\Model\Template\Config\Data as TemplateConfigData;

/**
 * Class LocalTemplate
 */
class LocalTemplate extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var TemplateCollection
     */
    protected $templateCollection;

    /**
     * @var TemplateConfig
     */
    protected $emailConfig;

    /**
     * @var TemplateConfigData
     */
    protected $templateConfigData;

    /**
     * LocalTemplate constructor.
     * @param TemplateCollection $templateCollection
     * @param TemplateConfig $emailConfig
     * @param array $data
     */
    public function __construct(
        TemplateCollection $templateCollection,
        TemplateConfig $emailConfig,
        TemplateConfigData $templateConfigData,
        array $data = []
    ) {
        parent::__construct($data);
        $this->templateCollection = $templateCollection;
        $this->emailConfig = $emailConfig;
        $this->templateConfigData = $templateConfigData;
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => __('Do not use')
            ]
        ];
        foreach ($this->templateCollection->toOptionHash() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        $allTemplates = $this->templateConfigData->get();
        $pdfTemplate = array_filter($allTemplates, function($elem){
            if (isset($elem['module']) && $elem['module'] === 'Potato_Pdf') {
                return true;
            }
            return false;
        });
        foreach ($pdfTemplate as $key => $item) {
            $options[] = ['value' => $key, 'label' => $item['label']];
        }

        return $options;
    }
    
}