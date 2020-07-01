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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Email\Model\UnsubscriptionFactory;
use Mirasvit\Email\Controller\Adminhtml\Unsubscription;

class Unsubscribe extends Unsubscription
{

    /**
     * @var UnsubscriptionFactory
     */
    protected $unsubscriptionFactory;

    /**
     * @param UnsubscriptionFactory $unsubscriptionFactory
     * @param Registry              $registry
     * @param Context               $context
     * @param ForwardFactory        $resultForwardFactory
     */
    public function __construct(
        UnsubscriptionFactory $unsubscriptionFactory,
        Registry              $registry,
        Context               $context,
        ForwardFactory        $resultForwardFactory
    ) {
        $this->unsubscriptionFactory = $unsubscriptionFactory;

        parent::__construct($unsubscriptionFactory, $registry, $context, $resultForwardFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getParams();

        if ($data) {
            $emails  = explode(',', $this->getRequest()->getParam('unsubscription_email'));
            $triggers = $this->getRequest()->getParam('trigger_ids');
            try {
                foreach ($emails as $email) {
                    foreach ($triggers as $triggerId) {
                        $model = $this->unsubscriptionFactory->create();
                        $model->unsubscribe($email, $triggerId);
                    }
                }

                $this->messageManager->addSuccess(__('You added new unsubscription(s) for emails: %1.', implode(', ', $emails)));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            }
        } else {
            $this->messageManager->addError(__('No data to save.'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
