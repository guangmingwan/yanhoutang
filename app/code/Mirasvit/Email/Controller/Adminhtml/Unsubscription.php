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



namespace Mirasvit\Email\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Email\Model\UnsubscriptionFactory;

abstract class Unsubscription extends Action
{
    /**
     * @var UnsubscriptionFactory
     */
    protected $unsubscriptionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Constructor
     *
     * @param UnsubscriptionFactory    $unsubscriptionFactory
     * @param Registry                 $registry
     * @param Context                  $context
     * @param ForwardFactory           $resultForwardFactory
     */
    public function __construct(
        UnsubscriptionFactory    $unsubscriptionFactory,
        Registry                 $registry,
        Context                  $context,
        ForwardFactory           $resultForwardFactory
    ) {
        $this->unsubscriptionFactory = $unsubscriptionFactory;
        $this->registry              = $registry;
        $this->context               = $context;
        $this->resultForwardFactory  = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Backend::marketing');
        $resultPage->getConfig()->getTitle()->prepend(__('Follow Up Email'));
        $resultPage->getConfig()->getTitle()->prepend(__('Unsubscription List'));

        return $resultPage;
    }

    /**
     * Current unsubscription model
     *
     * @return \Mirasvit\Email\Model\Unsubscription
     */
    public function initModel()
    {
        $model = $this->unsubscriptionFactory->create();

        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Email::unsubscription');
    }
}
