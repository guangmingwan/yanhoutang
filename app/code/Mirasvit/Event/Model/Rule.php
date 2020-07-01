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



namespace Mirasvit\Event\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var Rule\Action\CollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * @var Rule\Condition\CombineFactory
     */
    private $conditionCombineFactory;

    /**
     * @var mixed
     */
    private $eventIdentifier;

    /**
     * Rule constructor.
     * @param Rule\Action\CollectionFactory $actionCollectionFactory
     * @param Rule\Condition\CombineFactory $conditionCombineFactory
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param null $resource
     * @param null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Rule\Action\CollectionFactory $actionCollectionFactory,
        Rule\Condition\CombineFactory $conditionCombineFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        $resource = null,
        $resourceCollection = null,
        array $data = []
    ) {
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->conditionCombineFactory = $conditionCombineFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection|Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine|Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->conditionCombineFactory->create([
            'eventIdentifier' => $this->eventIdentifier,
        ]);
    }

    /**
     * @param string $identifier
     * @return $this
     */
    public function setEventIdentifier($identifier)
    {
        $this->eventIdentifier = $identifier;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toString($format = '')
    {
        $string = $this->getConditions()->asStringRecursive();
        if (count(explode("\n", $string)) > 1) {
            $string = nl2br(preg_replace('/ /', '&nbsp;', $string));
            return $string;
        }

        return "";
    }
}
