<?php

namespace Potato\Pdf\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface TemplateSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Potato\Pdf\Api\Data\TemplateInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Potato\Pdf\Api\Data\TemplateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
