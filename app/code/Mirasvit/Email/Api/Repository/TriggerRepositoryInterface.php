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
use Mirasvit\Email\Api\Data\TriggerInterface;

interface TriggerRepositoryInterface
{
    /**
     * @return TriggerInterface[]|\Mirasvit\Email\Model\ResourceModel\Trigger\Collection
     */
    public function getCollection();

    /**
     * @return TriggerInterface
     */
    public function create();

    /**
     * @param int $id
     *
     * @return TriggerInterface|false
     */
    public function get($id);

    /**
     * @param TriggerInterface $model
     * @return TriggerInterface
     */
    public function save(TriggerInterface $model);

    /**
     * @param TriggerInterface $model
     * @return bool
     */
    public function delete(TriggerInterface $model);
}
