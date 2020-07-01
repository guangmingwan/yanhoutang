<?php

namespace Potato\Pdf\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\Template;

/**
 * Class Delete
 */
class Delete extends Template
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $template = $this->templateRepository->get($id);
                $this->templateRepository->delete($template);
                $this->messageManager->addSuccessMessage(__('Template was been successfully deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('This template no longer exists.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
