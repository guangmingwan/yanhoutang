<?php

namespace Potato\Pdf\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Potato\Pdf\Api\Data\TemplateInterface;

/**
 * Class Template
 */
class Template extends AbstractExtensibleObject implements TemplateInterface
{
    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * @param string|null $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }
        
    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->_get(self::CONTENT);
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @api
     * @return array
     */
    public function toArray()
    {
        return $this->__toArray();
    }
}
