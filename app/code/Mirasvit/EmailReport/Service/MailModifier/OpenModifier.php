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
 * @package   mirasvit/module-email-report
 * @version   2.0.10
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Service\MailModifier;

use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Service\Queue\MailModifierInterface;
use Magento\Framework\UrlInterface;
use Mirasvit\EmailReport\Api\Service\StorageServiceInterface;

class OpenModifier implements MailModifierInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * OpenModifier constructor.
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param QueueInterface $queue
     * @param string $content
     * @return string|string[]
     */
    public function modifyContent(QueueInterface $queue, $content)
    {
        $params = [
            StorageServiceInterface::QUEUE_PARAM_NAME => $queue->getUniqHash(),
        ];

        $imgUrl = $this->urlBuilder->getUrl('emailreport/track/open', $params);

        $content = str_replace('</body>', '<img src="' . $imgUrl . '"></body>', $content);

        return $content;
    }
}
