<?php
namespace Potato\Pdf\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Potato\Pdf\Controller\Adminhtml\Template;

/**
 * Class Index
 */
class Index extends Template
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Template'));
    
        return $resultPage;
    }
}