<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email
 * @version   2.1.41
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Email\Controller\Adminhtml\Unsubscription;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Email\Controller\Adminhtml\Unsubscription;

class Edit extends Unsubscription
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id    = $this->getRequest()->getParam('id');
        $model = $this->initModel();

        if ($id && !$model->getId()) {
            $this->messageManager->addError(__('This item not exists.'));

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Unsubscription'));

        return $resultPage;
    }
}
