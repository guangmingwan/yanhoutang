<?php

namespace Potato\Pdf\Model\Email;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param  string $filename
     * @param  string $content
     * @param  string $type
     * @param  string $disposition
     * @param  string $encoding
     */
    public function addAttachment($filename, $content, $type, $disposition = null, $encoding = null)
    {
        $this->message->createAttachment(
            $content,
            $type,
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $filename
        );
    }
}
