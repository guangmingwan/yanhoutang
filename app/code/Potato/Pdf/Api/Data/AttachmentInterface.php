<?php

namespace Potato\Pdf\Api\Data;

/**
 * @api
 */
interface AttachmentInterface
{
    const FILENAME = 'file_name';
    const CONTENT = 'content';
    const TYPE = 'type';
    const DISPOSITION = 'disposition';
    const ENCODING = '$encoding';

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return null|string
     */
    public function getDisposition();

    /**
     * @param string $disposition
     * @return $this
     */
    public function setDisposition($disposition);

    /**
     * @return null|string
     */
    public function getEncoding();

    /**
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding);
}
