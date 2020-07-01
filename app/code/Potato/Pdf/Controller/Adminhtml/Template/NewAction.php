<?php

namespace Potato\Pdf\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\Template;

/**
 * Class NewAction
 */
class NewAction extends Template
{
    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('edit');
    }
}
