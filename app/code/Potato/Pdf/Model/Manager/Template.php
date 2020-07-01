<?php

namespace Potato\Pdf\Model\Manager;

use Potato\Pdf\Api\Data\TemplateInterface;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Psr\Log\LoggerInterface;
use Potato\Pdf\Model\Config;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Potato\Pdf\Model\App\Pdf;
use Potato\Pdf\Api\TemplateRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Email\Model\Template\Config as TemplateConfig;
use Potato\Pdf\Model\Template\Filter as TemplateFilter;
use Magento\Framework\Registry;

/**
 * Class Template
 */
class Template
{
    const TMP_FOLDER = 'pub/media/po_pdf';

    /** @var LoggerInterface  */
    protected $logger;

    /** @var EmailTemplateFactory  */
    protected $emailTemplateFactory;

    /** @var Config  */
    protected $config;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var Pdf  */
    protected $service;

    /** @var TemplateRepositoryInterface  */
    protected $templateRepository;

    /** @var TemplateConfig  */
    protected $emailConfig;

    /** @var TemplateFilter  */
    protected $templateFilter;

    /** @var Registry  */
    protected $registry;

    /**
     * Template constructor.
     * @param EmailTemplateFactory $emailTemplateFactory
     * @param Config $config
     * @param LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param Pdf $service
     * @param TemplateRepositoryInterface $templateRepository
     * @param TemplateConfig $emailConfig
     * @param TemplateFilter $templateFilter
     * @param Registry $registry
     */
    public function __construct(
        EmailTemplateFactory $emailTemplateFactory,
        Config $config,
        LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Pdf $service,
        TemplateRepositoryInterface $templateRepository,
        TemplateConfig $emailConfig,
        TemplateFilter $templateFilter,
        Registry $registry
    ) {
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->service = $service;
        $this->templateRepository = $templateRepository;
        $this->emailConfig = $emailConfig;
        $this->templateFilter = $templateFilter;
        $this->registry = $registry;
    }

    /**
     * @param array $htmlList
     * @param int $storeId
     * @return bool|string
     * @throws \Exception
     */
    public function processPdf($htmlList, $storeId = null)
    {
        if (!is_array($htmlList) || empty($htmlList)) {
            return false;
        }
        $htmlFiles = [];

        $options = [
            'margin_left' => $this->config->getMarginLeft($storeId),
            'margin_right' => $this->config->getMarginRight($storeId),
            'margin_bottom' => $this->config->getMarginBottom($storeId),
            'orientation' => $this->config->getPageOrientation($storeId),
            'margin_top' => $this->config->getMarginTop($storeId),
            'format' => $this->config->getPageFormat($storeId),
            'additional' => $this->config->getAdditionalOptions(),
            'html' => []
        ];

        if ($header = $this->registry->registry('header')) {
            list($htmlFilePath, $_htmlUrl) = $this->createTmpFile('<!DOCTYPE html>' . $header['content']);
            $options['additional'] .= ' --header-html ' . $_htmlUrl . ' --header-spacing 10 ';
            $options['margin_top'] += $header['height'];
            array_push($htmlFiles, $htmlFilePath);
            $this->registry->unregister('header');
        }
        if ($footer = $this->registry->registry('footer')) {
            list($htmlFilePath, $_htmlUrl) = $this->createTmpFile('<!DOCTYPE html>' . $footer['content']);
            $options['additional'] .= ' --footer-html ' . $_htmlUrl ;
            $options['margin_bottom'] += $footer['height'];
            array_push($htmlFiles, $htmlFilePath);
            $this->registry->unregister('footer');
        }

        $optionConvertedFiles = '';
        foreach ($htmlList as $html) {
            //create temp html file
            if (is_array($html)) {
                $html = implode('', $html);
            }
            list($htmlFilePath, $htmlUrl) = $this->createTmpFile($html);
            $options['html'][] = $htmlUrl;
            $optionConvertedFiles .= ' ' . $htmlUrl . ' ';
            $htmlFiles[] = $htmlFilePath;
        }

        //create temp pdf file
        $pdfFilePath = $this->getPdfTmpDir() . DIRECTORY_SEPARATOR . md5($optionConvertedFiles) . '.pdf';
        $result = [];
        $status = 0;

        //prepare options
        $optionsLine = ' -L ' . $options['margin_left']
            . ' -R ' . $options['margin_right']
            . ' -T ' . $options['margin_top']
            . ' -B ' . $options['margin_bottom']
            . ' -O ' . $options['orientation']
            . ' -s ' . $options['format']
            . ' ' . $options['additional']
        ;

        if ($this->config->canUseService($storeId)) {
            $pdf = $this->service->process($options);
            $result = file_put_contents($pdfFilePath, $pdf);
            if (false === $result) {
                throw new \Exception(__('Error writing file %1', $pdfFilePath));
            }
        } else {
            exec($this->config->getLibPath() . $optionsLine . $optionConvertedFiles . $pdfFilePath . ' 2>&1',
                $result,
                $status
            );
            $stringResult = implode(' ', $result);
            if (empty($result) || $status != 0) {
                throw new \Exception(__('Application for Print PDF files returns the error. Error code: %1 %2',
                    $status, $stringResult));
            }
        }
        
        if (is_readable($pdfFilePath)) {
            //remove tmp pdf file
            $pdfContent = file_get_contents($pdfFilePath);
            unlink($pdfFilePath);
        } else {
            throw new \Exception(__('File is not readable %1', $pdfFilePath));
        }
        
        foreach ($htmlFiles as $htmlFilePath) {
            $result = unlink($htmlFilePath);
            if (false === $result) {
                $this->logger->error(__("Can't delete temp file %1", $htmlFilePath));
            }
        }
        return $pdfContent;
    }

    public function createTmpFile($content)
    {
        $filename = md5($content) . '.html';
        $htmlFilePath = $this->getPdfTmpDir() . DIRECTORY_SEPARATOR . $filename;
        $result = file_put_contents($htmlFilePath,  chr(239) . chr(187) . chr(191) . $content);
        if (false === $result) {
            throw new \Exception(__('Error writing file %1', $htmlFilePath));
        }
        $htmlUrl = $this->getPdfTmpUrl($filename);
        return array($htmlFilePath, $htmlUrl);
    }

    public function getTemplateById($templateId)
    {
        try {
            $template = $this->templateRepository->get($templateId);
        } catch (NoSuchEntityException $e) {

            $emailTemplate = $this->emailTemplateFactory->create();
            $parts = $this->emailConfig->parseTemplateIdParts($templateId);
            $templateId = $parts['templateId'];
            $theme = $parts['theme'];

            if ($theme) {
                $emailTemplate->setForcedTheme($templateId, $theme);
            }
            
            $emailTemplate->setForcedArea($templateId);
            $emailTemplate->loadDefault($templateId);
            $template = $this->templateRepository->create([]);
            $template->setContent($emailTemplate->getTemplateText());
        }
        return $template;
    }

    /**
     * @param TemplateInterface $template
     * @param array $variables
     * @param int|null $storeId
     * @param bool $isNeedEmulation
     * @return string
     */
    public function getTemplateHtml(TemplateInterface $template, $variables, $storeId, $isNeedEmulation = false)
    {
        /** @var \Magento\Email\Model\Template $emailTemplate */
        $emailTemplate = $this->emailTemplateFactory->create();
        if ($isNeedEmulation && $storeId) {
            $emailTemplate->emulateDesign($storeId);
        }
        $emailTemplate->setTemplateText($template->getContent());
        $emailTemplate->setTemplateFilter($this->templateFilter);
        $result = $emailTemplate->getProcessedTemplate($variables);
        $emailTemplate->revertDesign();
        return $result;
    }

    private function getPdfTmpDir()
    {
        //prepare tmp folder
        $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        $pdfTmpDirPath = $baseDir . self::TMP_FOLDER;
        if (!is_dir($pdfTmpDirPath) && !mkdir($pdfTmpDirPath)) {
            throw new \Exception(__("Can't create directory %1", $pdfTmpDirPath));
        }
        return $pdfTmpDirPath;
    }

    private function getPdfTmpUrl($filename)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . 'po_pdf' . DIRECTORY_SEPARATOR . $filename;
    }
}