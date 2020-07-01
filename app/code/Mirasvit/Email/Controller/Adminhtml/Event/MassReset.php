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



namespace Mirasvit\Email\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Email\Api\Service\EventManagementInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Model\Trigger\Handler;

class MassReset extends Action
{
    /**
     * @var TriggerRepositoryInterface
     */
    protected $triggerRepository;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * MassValidate constructor.
     *
     * @param EventManagementInterface   $eventManagement
     * @param EventRepositoryInterface   $eventRepository
     * @param TriggerRepositoryInterface $triggerRepository
     * @param Handler                    $handler
     * @param Action\Context             $context
     */
    public function __construct(
        EventManagementInterface   $eventManagement,
        EventRepositoryInterface   $eventRepository,
        TriggerRepositoryInterface $triggerRepository,
        Handler                    $handler,
        Action\Context             $context
    ) {
        parent::__construct($context);
        $this->eventManagement   = $eventManagement;
        $this->eventRepository   = $eventRepository;
        $this->triggerRepository = $triggerRepository;
        $this->handler           = $handler;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $eventIds = $this->getRequest()->getParam('event_id');

        if (!is_array($eventIds)) {
            $this->messageManager->addErrorMessage(__('Please select event(s)'));
        } else {
            try {
                foreach ($eventIds as $eventId) {
                    $event = $this->eventRepository->get($eventId);
                    if ($event->getId()) {
                        $this->eventManagement->removeProcessedTriggers($event->getId());
                        $triggers = $this->triggerRepository->getCollection()->addActiveFilter();
                        foreach ($triggers as $trigger) {
                            $this->handler->handleEvents($trigger, [$event]);
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were reset', count($eventIds))
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
