<?php
namespace Potato\Pdf\Model\Email;

use Magento\Sales\Model\Order;
use Potato\Pdf\Api\Data\AttachmentInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Psr\Log\LoggerInterface;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    protected $logger;

    /**
     * SenderBuilder constructor.
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder);
        $this->logger = $logger;
    }
    
    protected function configureEmailTemplate()
    {
        $templateVars = $this->templateContainer->getTemplateVars();
        if (array_key_exists('po_pdf_attachment', $templateVars)) {
            /** @var AttachmentInterface $attachment */
            $attachment = $templateVars['po_pdf_attachment'];
            try {
                $this->transportBuilder->addAttachment(
                    $attachment->getFilename(),
                    $attachment->getContent(),
                    $attachment->getType(),
                    $attachment->getDisposition(),
                    $attachment->getEncoding()
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        parent::configureEmailTemplate();
    }
}
