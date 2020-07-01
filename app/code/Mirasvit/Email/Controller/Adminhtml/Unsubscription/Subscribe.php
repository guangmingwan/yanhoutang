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

use Mirasvit\Email\Controller\Adminhtml\Unsubscription;

class Subscribe extends Unsubscription
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $subscription = $this->initModel();
        $subscription->delete();
        
        $this->messageManager->addSuccessMessage(__('Email was subscribed.'));

        return $resultRedirect->setPath('*/*/');
    }
}
