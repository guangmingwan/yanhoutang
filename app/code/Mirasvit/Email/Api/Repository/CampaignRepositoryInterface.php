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

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Data\CampaignInterface;

interface CampaignRepositoryInterface
{
    /**
     * @return CampaignInterface[]|\Mirasvit\Email\Model\ResourceModel\Campaign\Collection
     */
    public function getCollection();

    /**
     * @return CampaignInterface
     */
    public function create();

    /**
     * @param int $id
     *
     * @return CampaignInterface|false
     */
    public function get($id);

    /**
     * @param CampaignInterface $model
     * @return CampaignInterface
     */
    public function save(CampaignInterface $model);

    /**
     * @param CampaignInterface $model
     * @return bool
     */
    public function delete(CampaignInterface $model);
}
