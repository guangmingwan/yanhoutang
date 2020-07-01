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
 * @package   mirasvit/module-email-report
 * @version   2.0.10
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Email\Api\Repository\RepositoryInterface;

class Reset extends Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mirasvit_Email::email_settings';

    /**
     * @var array<RepositoryInterface>
     */
    private $reportRepositories;

    /**
     * Reset constructor.
     * @param Action\Context $context
     * @param array $reportRepositories
     */
    public function __construct(
        Action\Context $context,
        array $reportRepositories = []
    ) {
        $this->reportRepositories = $reportRepositories;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/system_config/edit/section/email');
        try {
            foreach ($this->reportRepositories as $reportRepo) {
                foreach ($reportRepo->getCollection() as $report) {
                    $reportRepo->delete($report);
                }
            }

            $this->messageManager->addSuccessMessage(__('Statistic successfully cleared.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error occurred during statistic reset: %1', $e->getMessage()));
        }

        return $resultRedirect;
    }
}
