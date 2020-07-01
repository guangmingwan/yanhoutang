<?php

namespace Potato\Pdf\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface TemplateRepositoryInterface
{
    /**
     * Create Template service
     *
     * @param \Potato\Pdf\Api\Data\TemplateInterface $template
     * @return \Potato\Pdf\Api\Data\TemplateInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Data\TemplateInterface $template);

    /**
     * Get info about Template by template id
     *
     * @param int $templateId
     * @return \Potato\Pdf\Api\Data\TemplateInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($templateId);

    /**
     * Delete template
     *
     * @param \Potato\Pdf\Api\Data\TemplateInterface $template
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(Data\TemplateInterface $template);

    /**
     * Create new item
     *
     * @param array $data
     * @return \Potato\Pdf\Api\Data\TemplateInterface
     */
    public function create($data);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @return mixed
     */
    public function getAllTemplates();
}
