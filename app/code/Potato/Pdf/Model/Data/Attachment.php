<?php

namespace Potato\Pdf\Model\Data;

use Potato\Pdf\Api\Data\AttachmentInterface;
use Magento\Framework\DataObject;

/**
 * Class Authorization
 */
class Attachment extends DataObject implements AttachmentInterface
{
    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->getData(AttachmentInterface::FILENAME);
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        return $this->setData(AttachmentInterface::FILENAME, $filename);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getData(AttachmentInterface::CONTENT);
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(AttachmentInterface::CONTENT, $content);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getData(AttachmentInterface::TYPE);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(AttachmentInterface::TYPE, $type);
    }

    /**
     * @return null|string
     */
    public function getDisposition()
    {
        return $this->getData(AttachmentInterface::DISPOSITION);
    }

    /**
     * @param string $disposition
     * @return $this
     */
    public function setDisposition($disposition)
    {
        return $this->setData(AttachmentInterface::DISPOSITION, $disposition);
    }

    /**
     * @return null|string
     */
    public function getEncoding()
    {
        return $this->getData(AttachmentInterface::ENCODING);
    }

    /**
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        return $this->setData(AttachmentInterface::ENCODING, $encoding);
    }
}