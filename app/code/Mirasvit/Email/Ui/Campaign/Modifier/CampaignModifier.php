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



namespace Mirasvit\Email\Ui\Campaign\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Magento\Framework\UrlInterface;

class CampaignModifier implements ModifierInterface
{
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var PoolInterface
     */
    private $modifierPool;

    /**
     * CampaignModifier constructor.
     * @param TriggerRepositoryInterface $triggerRepository
     * @param UrlInterface $urlBuilder
     * @param PoolInterface|null $modifierPool
     */
    public function __construct(
        TriggerRepositoryInterface $triggerRepository,
        UrlInterface $urlBuilder,
        PoolInterface $modifierPool = null
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->urlBuilder = $urlBuilder;
        $this->modifierPool = $modifierPool;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $data['view_url'] = $this->urlBuilder->getUrl(
            'email/campaign/view',
            [CampaignInterface::ID => $data[CampaignInterface::ID]]
        );
        $data['delete_url'] = $this->urlBuilder->getUrl(
            'email/campaign/delete',
            [CampaignInterface::ID => $data[CampaignInterface::ID]]
        );
        $data['duplicate_url'] = $this->urlBuilder->getUrl(
            'email/campaign/duplicate',
            [CampaignInterface::ID => $data[CampaignInterface::ID]]
        );

        $data = $this->addTriggers($data);

        return $data;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addTriggers($data)
    {
        $data['triggers'] = [];

        $collection = $this->triggerRepository->getCollection();
        $collection->addFieldToFilter(TriggerInterface::CAMPAIGN_ID, $data[CampaignInterface::ID]);

        foreach ($collection as $trigger) {
            $triggerData = [
                'id_field_name'         => TriggerInterface::ID,
                TriggerInterface::ID    => $trigger->getId(),
                TriggerInterface::TITLE => $trigger->getTitle(),
            ];

            foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
                $triggerData = $modifier->modifyData($triggerData);
            }

            $data['triggers'][] = $triggerData;
        }

        return $data;
    }
}
