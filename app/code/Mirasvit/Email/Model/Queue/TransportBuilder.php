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

use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\TemplateInterface;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var \Mirasvit\Email\Model\Queue\Template|EmailMessageInterface
     */
    protected $message;
    /**
     * Set message subject
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->getTemplate()->setSubject($subject);

        return $this;
    }

    /**
     * Set message body
     *
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->getTemplate()->setBody($body);

        if (method_exists($this->message, 'setBodyHtml')) {
            $this->message->setBodyHtml($body);
        }

        return $this;
    }

    /**
     * Set from
     *
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    public function setFrom($email, $name = null)
    {
        /** @var \Magento\Framework\Module\Manager $moduleManager */
        $moduleManager = $this->objectManager->get(\Magento\Framework\Module\Manager::class);
        if ($moduleManager->isEnabled('Mageplaza_Smtp') && is_array($email)) {
            $name = $email['name'];
            $email = $email['email'];
        }

        $this->getTemplate()->setFrom($email, $name);

        if (method_exists($this, 'setFromByScope')) {
            $this->setFromByScope('general');
        }

        return $this;
    }

    /**
     * Set message type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setMessageType($type)
    {
        $this->getTemplate()->setMessageType($type);

        return $this;
    }

    /**
     * @return $this|\Magento\Framework\Mail\TransportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTransport()
    {
        if ($this->message instanceof \Magento\Framework\Mail\MessageInterface) {
            // magento <= 2.3.2
            return parent::getTransport();
        } else {
            // magento 2.3.3+
            if ($this->message === null) {
                return $this;
            }

            return parent::getTransport();
        }
    }

    /**
     * @inheritdoc
     * Get template
     * @return TemplateInterface
     */
    protected function getTemplate()
    {
        if ($this->message == null || $this->message instanceof \Magento\Framework\Mail\EmailMessage) {
            $this->message = new Template();
        }

        return $this->message;
    }

    /**
     * @return $this|\Magento\Framework\Mail\Template\TransportBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareMessage()
    {
        if ($this->message instanceof \Magento\Framework\Mail\MessageInterface) {
            return $this;
        } else {
            return parent::prepareMessage();
        }
    }
}
