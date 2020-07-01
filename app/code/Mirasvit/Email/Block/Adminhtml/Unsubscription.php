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



namespace Mirasvit\Email\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Unsubscription extends Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_unsubscription';
        $this->_blockGroup = 'Mirasvit_Email';
        $this->_headerText = __('Unsubscription List');
        $this->_addButtonLabel = __('Unsubscribe Email');

        parent::_construct();
        
        return $this;
    }
}
