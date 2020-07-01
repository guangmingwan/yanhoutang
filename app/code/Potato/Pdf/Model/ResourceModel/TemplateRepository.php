<?php

namespace Potato\Pdf\Model\ResourceModel;

use Magento\Framework\Api;
use Potato\Pdf\Api as PdfApi;
use Potato\Pdf\Model as PdfModel;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class TemplateRepository
 */
class TemplateRepository implements PdfApi\TemplateRepositoryInterface
{
    /** @var PdfModel\TemplateFactory  */
    protected $templateFactory;

    /** @var PdfModel\TemplateRegistry  */
    protected $templateRegistry;

    /** @var PdfApi\Data\TemplateSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var Api\ExtensibleDataObjectConverter  */
    protected $extensibleDataObjectConverter;

    /** @var Template  */
    protected $templateResource;

    /** @var PdfApi\Data\TemplateInterfaceFactory  */
    protected $dataFactory;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var SearchCriteriaBuilder  */
    protected $searchBuilder;

    /**
     * TemplateRepository constructor.
     * @param PdfModel\TemplateFactory $templateFactory
     * @param PdfModel\TemplateRegistry $templateRegistry
     * @param PdfApi\Data\TemplateSearchResultsInterfaceFactory $searchResultsFactory
     * @param Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Template $templateResource
     * @param PdfApi\Data\TemplateInterfaceFactory $dataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        PdfModel\TemplateFactory $templateFactory,
        PdfModel\TemplateRegistry $templateRegistry,
        PdfApi\Data\TemplateSearchResultsInterfaceFactory $searchResultsFactory,
        Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Template $templateResource,
        PdfApi\Data\TemplateInterfaceFactory $dataFactory,
        DataObjectHelper $dataObjectHelper,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRegistry = $templateRegistry;
        $this->templateResource = $templateResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->dataFactory = $dataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * @param \Potato\Pdf\Api\Data\TemplateInterface $template
     * @return Template
     */
    public function save(PdfApi\Data\TemplateInterface $template)
    {
        $templateData = $this->extensibleDataObjectConverter->toNestedArray(
            $template,
            [],
            PdfApi\Data\TemplateInterface::class
        );
        $templateModel = $this->templateFactory->create();
        $templateModel->addData($templateData);
        $templateModel->setId($template->getId());
        $this->templateResource->save($templateModel);
        $this->templateRegistry->push($templateModel);
        $savedObject = $this->get($templateModel->getId());
        return $savedObject;
    }

    /**
     * @param int $templateId
     * @return PdfApi\Data\TemplateInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($templateId)
    {
        $templateModel = $this->templateRegistry->retrieve($templateId);
        return $templateModel->getDataModel();
    }

    /**
     * @param \Potato\Pdf\Api\Data\TemplateInterface $template
     * @return $this
     */
    public function delete(PdfApi\Data\TemplateInterface $template)
    {
        return $this->deleteById($template->getId());
    }

    /**
     * @param int $templateId
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($templateId)
    {
        $templateModel = $this->templateRegistry->retrieve($templateId);
        $templateModel->getResource()->delete($templateModel);
        $this->templateRegistry->remove($templateId);
        return true;
    }

    /**
     * @param Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->templateFactory->create()->getCollection();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];

            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var Api\SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $templates = [];
        foreach ($collection as $templateModel) {
            $templates[] = $templateModel->getDataModel();
        }
        $searchResults->setItems($templates);
        return $searchResults;
    }

    /**
     * Create new item
     * 
     * @param array $data
     * @return PdfApi\Data\TemplateInterface
     */
    public function create($data)
    {
        /** @var PdfApi\Data\TemplateInterface $item */
        $item = $this->dataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $item,
            $data,
            PdfApi\Data\TemplateInterface::class
        );

        return $item;
    }

    /**
     * @return mixed
     */
    public function getAllTemplates()
    {
        $criteria = $this->searchBuilder->create();
        return $this->getList($criteria);
    }
}
