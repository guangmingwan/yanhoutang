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



namespace Mirasvit\Email\Controller\Adminhtml\Campaign;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Email\Controller\Adminhtml\Campaign;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Repository\RepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Service\CloneServiceInterface;

class Duplicate extends Campaign
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            $model = $this->campaignRepository->get($this->getRequest()->getParam(CampaignInterface::ID));

            try {
                $this->duplicate($model);

                $this->messageManager->addSuccessMessage(__('Campaign was successfully duplicated.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to find campaign to duplicate'));

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param CampaignInterface $campaign
     *
     * @return AbstractModel
     */
    private function duplicate(CampaignInterface $campaign)
    {
        /** @var CloneServiceInterface $clonner */
        $clonner = $this->_objectManager->get(CloneServiceInterface::class);

        $campaignClone = $clonner->duplicate($campaign, $this->campaignRepository, [
            CampaignInterface::ID,
            CampaignInterface::CREATED_AT,
            CampaignInterface::UPDATED_AT
        ]);

        /** @var TriggerRepositoryInterface|RepositoryInterface $triggerRepository */
        $triggerRepository = $this->_objectManager->get(TriggerRepositoryInterface::class);
        /** @var ChainRepositoryInterface|RepositoryInterface $chainRepository */
        $chainRepository = $this->_objectManager->get(ChainRepositoryInterface::class);

        // Retrieve triggers related with original campaign
        $triggerCollection = $triggerRepository->getCollection();
        $triggerCollection->addFieldToFilter(CampaignInterface::ID, $campaign->getId());

        // Duplicate triggers for campaign clone
        /** @var TriggerInterface $trigger */
        foreach ($triggerCollection as $trigger) {
            $triggerClone = $clonner->duplicate($trigger, $triggerRepository, [
                TriggerInterface::ID,
                TriggerInterface::CREATED_AT,
                TriggerInterface::UPDATED_AT
            ], [CampaignInterface::ID => $campaignClone->getId()]);

            // duplicate chains for trigger clone
            /** @var ChainInterface $chain */
            foreach ($trigger->getChainCollection() as $chain) {
                $clonner->duplicate($chain, $chainRepository, [
                    ChainInterface::ID,
                    ChainInterface::CREATED_AT,
                    ChainInterface::UPDATED_AT
                ], [TriggerInterface::ID => $triggerClone->getId()]);
            }
        }

        return $campaignClone;
    }
}
