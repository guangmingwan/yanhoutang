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



namespace Mirasvit\Email\Block\Adminhtml\Unsubscription;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Model\Queue;
use Mirasvit\Email\Model\Config\Source\Triggers;
use Mirasvit\Email\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Mirasvit\Email\Model\ResourceModel\Trigger\CollectionFactory as TriggerCollectionFactory;
use Mirasvit\Email\Model\ResourceModel\Unsubscription\CollectionFactory as UnsubscriptionCollectionFactory;
use Magento\Newsletter\Model\SubscriberFactory;

class Grid extends GridExtended
{
   /**
    * @var SubscriberFactory
    */
    protected $subscriberFactory;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var TriggerCollectionFactory
     */
    protected $triggerCollectionFactory;

    /**
     * @var UnsubscriptionCollectionFactory
     */
    protected $unsubscriptionCollectionFactory;

    /**
     * @var Triggers
     */
    protected $triggers;

    /**
     * @var \Magento\Framework\Url
     */
    private $frontUrlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Url               $frontUrlBuilder
     * @param QueueCollectionFactory               $queueQueueCollectionFactory
     * @param TriggerCollectionFactory             $triggerCollectionFactory
     * @param Triggers                             $triggers
     * @param UnsubscriptionCollectionFactory      $unsubscriptionCollectionFactory
     * @param Context                              $context
     * @param BackendHelper                        $backendHelper
     */
    public function __construct(
        SubscriberFactory                       $subscriberFactory,
        \Magento\Framework\Url                  $frontUrlBuilder,
        QueueCollectionFactory                  $queueQueueCollectionFactory,
        TriggerCollectionFactory                $triggerCollectionFactory,
        Triggers                                $triggers,
        UnsubscriptionCollectionFactory         $unsubscriptionCollectionFactory,
        Context                                 $context,
        BackendHelper                           $backendHelper
    ) {
        $this->subscriberFactory               = $subscriberFactory;
        $this->frontUrlBuilder                 = $frontUrlBuilder;
        $this->queueCollectionFactory          = $queueQueueCollectionFactory;
        $this->triggerCollectionFactory        = $triggerCollectionFactory;
        $this->triggers                        = $triggers;
        $this->unsubscriptionCollectionFactory = $unsubscriptionCollectionFactory;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('email_unsubscription_grid');
        $this->setDefaultSort('unsubscription_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->unsubscriptionCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('unsubscription_id', [
            'header' => __('ID'),
            'type'   => 'number',
            'index'  => 'unsubscription_id',
        ]);

        $this->addColumn('trigger_id', [
            'header'   => __('Trigger'),
            'index'    => 'trigger_id',
             'type'    => 'options',
             'options' => $this->triggers->toArray()
        ]);

        $this->addColumn('updated_at', [
            'header' => __('Updated At'),
            'type'   => 'datetime',
            'index'  => 'updated_at',
        ]);

        $this->addColumn('email', [
            'header' => __('Email'),
            'index'  => 'email',
        ]);

        $this->addColumn('action', [
            'header'    => __('Action'),
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Subscribe'),
                    'url'     => ['base' => '*/*/subscribe'],
                    'field'   => 'id',
                    'confirm' => __('Are you sure?'),
                ],
            ],
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('unsubscription_id');
        $this->getMassactionBlock()->setFormFieldName('unsubscription_id');

        $this->getMassactionBlock()->addItem('mass_subscription', [
            'label'   => __('Subscribe'),
            'url'     => $this->getUrl('*/*/massSubscribe'),
            'confirm' => __('Are you sure?'),
        ]);

        return $this;
    }
}
