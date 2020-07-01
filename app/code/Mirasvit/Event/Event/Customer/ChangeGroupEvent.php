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



namespace Mirasvit\Event\Event\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\CustomerData;
use Mirasvit\Event\EventData\StoreData;
use Mirasvit\Event\Event\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;

class ChangeGroupEvent extends ObservableEvent
{
    const IDENTIFIER        = 'change_group';

    const PARAM_CUSTOMER_ID = 'customer_id';

    /**
     * @var Session
     */
    protected $session;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Customer
     */
    private $customer;

    /**
     * ChangeGroupEvent constructor.
     * @param RequestInterface $request
     * @param Customer $customer
     * @param Session $session
     * @param Context $context
     */
    public function __construct(
        RequestInterface $request,
        Customer         $customer,
        Session          $session,
        Context          $context
    ) {
        $this->request  = $request;
        $this->customer = $customer;
        $this->session  = $session;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER  => __('Customer / Change Group'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(CustomerData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $customer = $this->context->create(CustomerData::class)->load($params[CustomerData::ID]);
        $store = $this->context->create(StoreData::class)->load($params[self::PARAM_STORE_ID]);

        $params[CustomerData::IDENTIFIER] = $customer;
        $params[StoreData::IDENTIFIER] = $store;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        return __('Customer Change Group');
    }

    /**
     * @param CustomerInterface $subject
     */
    public function beforeSetGroupId(CustomerInterface $subject)
    {
        $subject = $this->request->getParam('customer');

        if (isset($subject['entity_id']) && $subject['entity_id']) {
            $params = [
                self::PARAM_CUSTOMER_ID    => $subject['entity_id'],
                self::PARAM_CUSTOMER_EMAIL => $subject['email'],
                self::PARAM_CUSTOMER_NAME  => $subject['firstname'] . ' ' . $subject['lastname'],
                self::PARAM_STORE_ID       => $subject['store_id'],
                self::PARAM_EXPIRE_AFTER   => 60 * 60 * 24 // expires after 1 day
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$subject['entity_id']],
                $params
            );
        }
    }
}
