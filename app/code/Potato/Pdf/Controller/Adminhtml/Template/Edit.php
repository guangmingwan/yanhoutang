<?php

namespace Potato\Pdf\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;
use Potato\Pdf\Controller\Adminhtml\Template;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit
 */
class Edit extends Template
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        
        $id = $this->getRequest()->getParam('id', 0);
        try {
            $template = $this->templateRepository->get($id);
        } catch (\Exception $e) {
            $template = null;
        }

        if (!$template && 0 !== $id) {
            $this->messageManager->addErrorMessage(__('This template no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($template) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Template "%1"', $template->getTitle()));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Template'));
        }
        return $resultPage;
    }
}
