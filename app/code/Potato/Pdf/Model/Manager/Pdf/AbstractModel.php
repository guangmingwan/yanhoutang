<?php

namespace Potato\Pdf\Model\Manager\Pdf;

use Magento\Framework\App\Response\Http\FileFactory;
use Potato\Pdf\Model\Config;
use Potato\Pdf\Model\Variables;
use Potato\Pdf\Model\Manager\Template as TemplateManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class AbstractModel
 */
abstract class AbstractModel
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Variables
     */
    protected $variables;

    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * AbstractModel constructor.
     * @param FileFactory $fileFactory
     * @param Config $config
     * @param Variables $variables
     * @param TemplateManager $templateManager
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        FileFactory $fileFactory,
        Config $config,
        Variables $variables,
        TemplateManager $templateManager,
        MessageManagerInterface $messageManager
    ) {
        $this->fileFactory = $fileFactory;
        $this->config = $config;
        $this->variables = $variables;
        $this->templateManager = $templateManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $collection
     * @param bool $isBackend
     * @param bool $isNeedEmulation
     * @return array
     */
    abstract public function getPdfHtmlContentByCollection($collection, $isBackend = true, $isNeedEmulation = true);

    /**
     * @param mixed $entity
     * @param bool $isBackend
     * @param bool $isNeedEmulation
     * @return string
     */
    abstract public function getPdfHtmlContentByEntityId($entity, $isBackend = true, $isNeedEmulation = true);

    /**
     * @param array $html
     * @param int|null $storeId
     * @return string
     */
    public function convertHtmlToPdf($html, $storeId = null)
    {
        return $this->templateManager->processPdf($html, $storeId);
    }
}
