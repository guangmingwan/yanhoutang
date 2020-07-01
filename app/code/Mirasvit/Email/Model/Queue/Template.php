<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email
 * @version   2.1.41
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Model\Queue;

use Magento\Framework\Mail\TemplateInterface;

class Template implements TemplateInterface
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @param array $options
     * @return TemplateInterface|void
     */
    public function setOptions(array $options)
    {
    }

    /**
     * @param array $vars
     * @return TemplateInterface|void
     */
    public function setVars(array $vars)
    {
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $value
     */
    public function setSubject($value)
    {
        $this->subject = $value;
    }

    /**
     * @param string $value
     */
    public function setBody($value)
    {
        $this->body = $value;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return 2;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setMessageType($type)
    {
        return $this;
    }

    /**
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function setFrom($email, $name)
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isPlain()
    {
        return false;
    }

    /**
     * @return string
     */
    public function processTemplate()
    {
        return $this->body;
    }
}
