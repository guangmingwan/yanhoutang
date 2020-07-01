<?php

namespace Potato\Pdf\Model\Manager\Pdf;

use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Potato\Pdf\Model\Variables;
use Potato\Pdf\Model\Manager\Template as TemplateManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class Shipment
 */
class Shipment extends AbstractModel
{
    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * Order constructor.
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param Variables $variables
     * @param TemplateManager $templateManager
     * @param MessageManagerInterface $messageManager
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        FileFactory $fileFactory,
        Config $config,
        Variables $variables,
        TemplateManager $templateManager,
        MessageManagerInterface $messageManager,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        parent::__construct($fileFactory, $config, $variables, $templateManager, $messageManager);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param $collection
     * @param bool $isBackend
     * @param bool $isNeedEmulation
     * @return array
     */
    public function getPdfHtmlContentByCollection($collection, $isBackend = true, $isNeedEmulation = true)
    {
        $htmlListContent = [];
        foreach ($collection as $item) {
            $entityId = $item->getEntityId();
            try {
                $entity = $this->shipmentRepository->get($entityId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                continue;
            }
            $htmlListContent[] = $this->getPdfHtmlContentByEntityId($entity, $isBackend, $isNeedEmulation);
        }
        return $htmlListContent;
    }

    /**
     * @param mixed $entity
     * @param bool $isBackend
     * @param bool $isNeedEmulation
     * @return string
     */
    public function getPdfHtmlContentByEntityId($entity, $isBackend = true, $isNeedEmulation = true)
    {
        $htmlContent = '';
        try {
            if ($isBackend) {
                $templateId = $this->config->getShipmentAdminTemplate($entity->getStoreId());
            } else {
                $templateId = $this->config->getShipmentCustomerTemplate($entity->getStoreId());
            }
            $template = $this->templateManager->getTemplateById($templateId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $htmlContent;
        }
        $variables = $this->variables->getShipmentVariables($entity);
        $htmlContent = $this->templateManager->getTemplateHtml($template, $variables, $entity->getStoreId(), $isNeedEmulation);
        return $htmlContent;
    }
}
