<?php

namespace Potato\Pdf\Model\Template;

use Potato\Pdf\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Potato\Pdf\Model\ResourceModel\Template\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param TemplateCollectionFactory $templateCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        TemplateCollectionFactory $templateCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $templateCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $this->loadedData = [];
        foreach ($items as $item) {
            $result = $item->getData();
            $this->loadedData[$item->getId()] = $result;
        }
        $data = $this->getSession()->getFormData();
        if (!empty($data)) {
            $item = new \Magento\Framework\DataObject($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->getSession()->unsFormData();
        }
        return $this->loadedData;
    }

    /**
     * Get session object
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get('Magento\Framework\Session\SessionManagerInterface');
        }
        return $this->session;
    }
}
