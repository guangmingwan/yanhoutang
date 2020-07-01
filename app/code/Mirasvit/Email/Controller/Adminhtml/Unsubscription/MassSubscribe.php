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

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Email\Model\UnsubscriptionFactory;

class MassSubscribe extends Action
{
    /**
     * @var UnsubscriptionFactory
     */
    protected $unsubscriptionFactory;

    /**
     * MassValidate constructor.
     *
     * @param UnsubscriptionFactory    $unsubscriptionFactory
     * @param Action\Context           $context
     */
    public function __construct(
        UnsubscriptionFactory          $unsubscriptionFactory,
        Action\Context                 $context
    ) {
        $this->unsubscriptionFactory = $unsubscriptionFactory;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $unsubscriptionIds = $this->getRequest()->getParam('unsubscription_id');
        if (!is_array($unsubscriptionIds)) {
            $this->messageManager->addErrorMessage(__('Please select event(s)'));
        } else {
            try {
                foreach ($unsubscriptionIds as $unsubscriptionId) {
                    $model = $this->unsubscriptionFactory->create();
                    $model->load($unsubscriptionId);
                    $model->delete();
                }

                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were removed', count($unsubscriptionIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
