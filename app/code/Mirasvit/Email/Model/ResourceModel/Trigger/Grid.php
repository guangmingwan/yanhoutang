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



namespace Mirasvit\Email\Model\ResourceModel\Trigger;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

/**
 * @SuppressWarnings(PHPMD)
 */
class Grid extends SearchResult
{
    /**
     * {@inheritdoc}
     */
    protected $document = Document::class;

    /**
     * Grid constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = TriggerInterface::TABLE_NAME,
        $resourceModel = \Mirasvit\Email\Model\ResourceModel\Trigger::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
}
