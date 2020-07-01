<?php

namespace Potato\Pdf\Model\Manager\Pdf;

use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Potato\Pdf\Model\Variables;
use Potato\Pdf\Model\Manager\Template as TemplateManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class Creditmemo
 */
class Creditmemo extends AbstractModel
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * Order constructor.
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param Variables $variables
     * @param TemplateManager $templateManager
     * @param MessageManagerInterface $messageManager
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        FileFactory $fileFactory,
        Config $config,
        Variables $variables,
        TemplateManager $templateManager,
        MessageManagerInterface $messageManager,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        parent::__construct($fileFactory, $config, $variables, $templateManager, $messageManager);
        $this->creditmemoRepository = $creditmemoRepository;
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
                $entity = $this->creditmemoRepository->get($entityId);
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
                $templateId = $this->config->getCreditMemoAdminTemplate($entity->getStoreId());
            } else {
                $templateId = $this->config->getCreditMemoCustomerTemplate($entity->getStoreId());
            }
            $template = $this->templateManager->getTemplateById($templateId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $htmlContent;
        }
        $variables = $this->variables->getCreditMemoVariables($entity);
        $htmlContent = $this->templateManager->getTemplateHtml($template, $variables, $entity->getStoreId(), $isNeedEmulation);
        return $htmlContent;
    }
}
