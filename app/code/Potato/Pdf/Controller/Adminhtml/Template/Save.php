<?php

namespace Potato\Pdf\Controller\Adminhtml\Template;

use Magento\Framework\Message\Error;
use Magento\Framework\Exception\LocalizedException;
use Potato\Pdf\Controller\Adminhtml\Template;

/**
 * Class Save
 */
class Save extends Template
{
     /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$postData) {
            $this->messageManager->addErrorMessage(__('Data not found.'));
            return $resultRedirect->setPath('*/*/index');
        }
        $postData = array_filter($postData);
        $template = $this->templateRepository->create($postData);
        try {
            $this->templateRepository->save($template);
            $this->messageManager->addSuccessMessage(__('Template was been successfully created.'));
        } catch (LocalizedException $e) {
            $this->addSessionErrorMessages($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the template.'));
        }
        $this->_getSession()->setFormData($postData);
        $redirectBack = $this->getRequest()->getParam('back', false);
        if (array_key_exists('id', $postData) && $redirectBack === 'edit') {
            $resultRedirect->setPath('*/*/edit', ['id' => $postData['id']]);
        } else {
            $resultRedirect->setPath('*/*/index');
        }
        return $resultRedirect;
    }

    /**
     * Add error messages
     *
     * @param string $messages
     * @return $this
     */
    protected function addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
        return $this;
    }
}
