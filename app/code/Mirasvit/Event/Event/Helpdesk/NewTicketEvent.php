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
 * @package   mirasvit/module-event
 * @version   1.2.33
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Event\Helpdesk;

use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\Helpdesk\TicketData;

class NewTicketEvent extends ObservableEvent
{
    const IDENTIFIER = 'hdmx_ticket_new';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * NewTicketEvent constructor.
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        Context $context
    ) {
        parent::__construct($context);

        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Mirasvit Helpdesk / New ticket'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(TicketData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        return __(
            'Ticket ID: #%1 \nSubject: %2 \nURL: %3',
            $params['code'],
            $params['subject'],
            $params['backend_url']
        );
    }

    /**
     * Plugin executing when ticket is saving.
     *
     * @param \Mirasvit\Helpdesk\Model\Ticket $ticket
     * @param \callable                       $proceed
     *
     * @return \Mirasvit\Helpdesk\Model\Ticket
     */
    public function aroundSave(\Mirasvit\Helpdesk\Model\Ticket $ticket, $proceed)
    {
        $isExists = (bool)$ticket->getId();

        /** @var \Mirasvit\Helpdesk\Model\Ticket $result */
        $result = $proceed();

        if ($isExists) {
            return $result;
        }
        $params = [
            TicketData::ID           => $result->getId(),
            'code'                   => $result->getCode(),
            'subject'                => $result->getSubject(),
            'backend_url'            => $result->getBackendUrl(),
            self::PARAM_EXPIRE_AFTER => 86400,
        ];

        $this->context->eventRepository->register(
            self::IDENTIFIER,
            [$result->getId()],
            $params
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->moduleManager->isEnabled('Mirasvit_Helpdesk');
    }
}
