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



namespace Mirasvit\Email\Controller\Action;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Api\Service\SenderInterface;
use Mirasvit\Email\Controller\Action;
use Mirasvit\Email\Service\FrontSessionInitiator;

class Send extends Action
{
    /**
     * @var ChainRepositoryInterface
     */
    private $chainRepository;

    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * @var FrontSessionInitiator
     */
    private $emailSessionManager;


    /**
     * @var SenderInterface
     */
    private $testSender;

    /**
     * Send constructor.
     *
     * @param SenderInterface            $testSender
     * @param FrontSessionInitiator      $emailSessionManager
     * @param ChainRepositoryInterface   $chainRepository
     * @param TriggerRepositoryInterface $triggerRepository
     * @param Context                    $context
     */
    public function __construct(
        SenderInterface            $testSender,
        FrontSessionInitiator      $emailSessionManager,
        ChainRepositoryInterface   $chainRepository,
        TriggerRepositoryInterface $triggerRepository,
        Context $context
    ) {
        parent::__construct($context);

        $this->testSender          = $testSender;
        $this->chainRepository     = $chainRepository;
        $this->triggerRepository   = $triggerRepository;
        $this->emailSessionManager = $emailSessionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->getRequest()->isAjax()) {
            return $resultPage->setData(['success' => false, 'message' => __('Operation is not allowed')]);
        }

        if (!$this->getRequest()->getParam('email')) {
            $resultPage->setData(['success' => false, 'message' => __('Please specify email address')]);

            return $resultPage;
        }

        $chain   = $this->chainRepository->get($this->getRequest()->getParam(ChainInterface::ID));
        $trigger = $this->triggerRepository->get($chain->getTriggerId());

        if (!$trigger->getEvent()) {
            $resultPage->setData(['success' => false, 'message' => __('Please specify trigger Event first')]);

            return $resultPage;
        }

        if ($chain->getId()) {
            try {
                $this->testSender->sendChain($chain, $this->getRequest()->getParam('email'));
            } catch (\Exception $e) {
                return $resultPage->setData(['success' => false, 'message' => $e->getMessage()]);
            }

            return $resultPage->setData(['success' => true, 'message' => __('Test email was successfully sent')]);
        }

        $resultPage->setData(['success' => false, 'message' => __('Unable to find email to send')]);

        return $resultPage;
    }
}
