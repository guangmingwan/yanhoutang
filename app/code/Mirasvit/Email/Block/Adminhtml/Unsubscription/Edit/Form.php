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


namespace Mirasvit\Email\Block\Adminhtml\Unsubscription\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Email\Model\Config\Source\Triggers;

class Form extends WidgetForm
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Triggers
     */
    protected $triggers;

    /**
     * @param FormFactory $formFactory
     * @param Registry    $registry
     * @param Context     $context
     * @param Triggers    $triggers
     */
    public function __construct(
        FormFactory $formFactory,
        Registry    $registry,
        Context     $context,
        Triggers    $triggers
    ) {
        $this->formFactory = $formFactory;
        $this->registry    = $registry;
        $this->triggers    = $triggers;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create([
            'data' => [
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/unsubscribe', ['id' => $this->getRequest()->getParam('id')]),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $general = $form->addFieldset('general', []);

        $general->addField('unsubscription_email', 'text', [
            'label'    => __('Set emails via comma to unsubscribe'),
            'required' => true,
            'name'     => 'unsubscription_email',
        ]);

        $general->addField('trigger_ids', 'multiselect', [
            'name'     => 'trigger_ids',
            'label'    => __('Triggers'),
            'required' => true,
            'values'   => $this->triggers->toOptionArray(),
        ]);

        $form->setAction($this->getUrl('*/*/unsubscribe'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
