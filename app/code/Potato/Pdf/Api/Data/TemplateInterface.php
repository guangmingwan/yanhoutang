<?php

namespace Potato\Pdf\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * @api
 */
interface TemplateInterface extends CustomAttributesDataInterface
{
    const ID = 'id';
    const TITLE = 'title';
    const TYPE = 'type';
    const CONTENT = 'content';
    
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id);
        
    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string|null $type
     * @return $this
     */
    public function setType($type);
        
    /**
     * @return string|null
     */
    public function getContent();

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent($content);
}
