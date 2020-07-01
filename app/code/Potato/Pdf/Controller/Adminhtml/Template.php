<?php

namespace Potato\Pdf\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Potato\Pdf\Api\TemplateRepositoryInterface;

/**
 * Class Template
 */
abstract class Template extends Action
{
    const ADMIN_RESOURCE = 'Potato_Pdf::po_pdf_template';

    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    /**
     * Template constructor.
     * @param Action\Context $context
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(
        Action\Context $context,
        TemplateRepositoryInterface $templateRepository
    ) {
        parent::__construct($context);
        $this->templateRepository = $templateRepository;
    }
}
