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



namespace Mirasvit\Email\Api\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Data\QueueInterface;

interface QueueRepositoryInterface
{
    /**
     * Retrieve queue.
     *
     * @param int $id
     *
     * @return QueueInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Retrieve queue by unique hash.
     *
     * @param string $uniqueHash
     *
     * @return QueueInterface
     */
    public function getByUniqueHash($uniqueHash);

    /**
     * Create or update a queue.
     *
     * @param QueueInterface $queue
     *
     * @return QueueInterface
     */
    public function save(QueueInterface $queue);

    /**
     * Delete queue.
     *
     * @param QueueInterface $queue
     *
     * @return bool true on success
     */
    public function delete(QueueInterface $queue);

    /**
     * Retrieve collection of queues.
     *
     * @return \Mirasvit\Email\Model\ResourceModel\Queue\Collection|QueueInterface[]
     */
    public function getCollection();

    /**
     * Create new queue.
     *
     * @return QueueInterface
     */
    public function create();
}
