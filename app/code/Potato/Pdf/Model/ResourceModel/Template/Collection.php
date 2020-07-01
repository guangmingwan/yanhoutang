<?php

namespace Potato\Pdf\Model\ResourceModel\Template;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\Pdf\Model;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Model\Template::class,
            Model\ResourceModel\Template::class
        );
    }
    
    public function addFilterByType($type)
    {
        $this->addFieldToFilter('type', [['eq' => $type], ['null' => true]]);
    }
}
