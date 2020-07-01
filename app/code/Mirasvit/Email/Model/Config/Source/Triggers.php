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



namespace Mirasvit\Email\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Mirasvit\Email\Model\ResourceModel\Trigger\CollectionFactory as TriggerCollectionFactory;

class Triggers implements ArrayInterface
{
    /**
     * @var TriggerCollectionFactory
     */
    protected $triggerCollectionFactory;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        TriggerCollectionFactory         $triggerCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry      $registry
    ) {
        $this->triggerCollectionFactory = $triggerCollectionFactory;
        $this->context                  = $context;
        $this->registry                 = $registry;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        $result = $this->triggerCollectionFactory->create()->toOptionHash();

        array_unshift($result, 'All Triggers');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $triggerCollection = $this->toArray();
        foreach ($triggerCollection as $key => $name) {
            $result[] = [
                'label' => $name,
                'value' => $key,
            ];
        }

        return $result;
    }
}
