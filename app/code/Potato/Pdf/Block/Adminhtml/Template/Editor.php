<?php

namespace Potato\Pdf\Block\Adminhtml\Template;

use Magento\Framework\View\Element\Template;
use Potato\Pdf\Api\TemplateRepositoryInterface;

class Editor extends Template
{
    protected $_template = 'Potato_Pdf::editor.phtml';
    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    /**
     * Editor constructor.
     * @param Template\Context $context
     * @param TemplateRepositoryInterface $templateRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        TemplateRepositoryInterface $templateRepository,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->templateRepository = $templateRepository;
    }

    public function getPreviewUrl()
    {
        return $this->getUrl('po_pdf/template/preview');
    }

    public function getContent()
    {
        $id = $this->getRequest()->getParam('id', 0);
        try {
            $template = $this->templateRepository->get($id);
            $content = $template->getContent();
        } catch (\Exception $e) {
            $content = '';
        }
        return $content;
    }
}