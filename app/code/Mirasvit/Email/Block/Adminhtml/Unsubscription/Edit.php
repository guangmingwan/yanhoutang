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
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     * @param Registry $registry
     * @param Context  $context
     */
    public function __construct(
        Registry $registry,
        Context  $context
    ) {
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'unsubscription_id';
        $this->_blockGroup = 'Mirasvit_email';
        $this->_controller = 'adminhtml_unsubscription';

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');
            $this->buttonList->add('close', [
                'label'   => __('Close Window'),
                'class'   => 'cancel',
                'onclick' => 'window.close()',
                'level'   => -1,
            ]);
        } else {
            $this->buttonList->remove('save');

            $this->getToolbar()->addChild(
                'save-split-button',
                'Magento\Backend\Block\Widget\Button\SplitButton',
                [
                    'id'           => 'save-split-button',
                    'label'        => __('Unsubscribe'),
                    'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'button_class' => 'widget-button-update',
                    'options'      => [
                        [
                            'id'             => 'save-button',
                            'label'          => __('Unsubscribe'),
                            'default'        => true,
                            'data_attribute' => [
                                'mage-init' => [
                                    'button' => [
                                        'event'  => 'saveAndContinueEdit',
                                        'target' => '#edit_form'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id'             => 'save-continue-button',
                            'label'          => __('Unsubscribe & Close'),
                            'data_attribute' => [
                                'mage-init' => [
                                    'button' => [
                                        'event'  => 'save',
                                        'target' => '#edit_form'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }

        $this->buttonList->update('save', 'label', __('Save'));
    }
}
