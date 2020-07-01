<?php

namespace Potato\Pdf\Controller\Adminhtml\Template;

use Potato\Pdf\Controller\Adminhtml\Template;
use Magento\Ui\Component\MassAction\Filter;
use Potato\Pdf\Model\ResourceModel\Template\Grid\Collection as GridCollection;
use Magento\Backend\App\Action;
use Potato\Pdf\Api\TemplateRepositoryInterface;

/**
 * Class MassDelete
 */
class MassDelete extends Template
{
    /**
     * @var GridCollection
     */
    protected $collection;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param TemplateRepositoryInterface $templateRepository
     * @param Filter $filter
     * @param GridCollection $collection
     */
    public function __construct(
        Action\Context $context,
        TemplateRepositoryInterface $templateRepository,
        Filter $filter,
        GridCollection $collection
    ) {
        parent::__construct($context, $templateRepository);
        $this->collection = $collection;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->collection = $this->filter->getCollection($this->collection);
        $count = 0;

        foreach ($this->collection->getItems() as $templateItem) {
            try {
                $template = $this->templateRepository->get($templateItem->getId());
                $this->templateRepository->delete($template);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
