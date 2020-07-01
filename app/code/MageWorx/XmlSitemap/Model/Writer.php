<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\UrlInterface;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Filesystem\Io\File;
use \Zend\Validator\Sitemap\Changefreq as ChangefreqValidator;
use \Zend\Validator\Sitemap\Lastmod as LastmodValidator;
use \Zend\Validator\Sitemap\Loc as LocationValidator;
use \Zend\Validator\Sitemap\Priority as PriorityValidator;
use \Magento\Store\Model\Store as StoreModel;
use \Magento\Framework\DataObject;
use \MageWorx\XmlSitemap\Model\LinkChecker;

/**
 * {@inheritdoc}
 */
class Writer implements WriterInterface
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $serverPath;

    /**
     * @var ClientFactory
     */
    protected $client;

    /**
     * @var ChangefreqValidator
     */
    protected $changefreqValidator;

    /**
     * @var LastmodValidator
     */
    protected $lastmodValidator;

    /**
     * @var LocationValidator
     */
    protected $locationValidator;

    /**
     * @var PriorityValidator
     */
    protected $priorityValidator;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var LinkChecker
     */
    protected $linkChecker;

    /**
     * @var File
     */
    protected $io;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $fileDir;

    /**
     * @var string
     */
    protected $tempFilePath;

    /**
     * @var bool
     */
    protected $useIndex = true;

    /**
     * @var int
     */
    protected $maxLinks = 50000;

    /**
     * @var int
     */
    protected $splitSize = 10000000;

    /**
     * @var int
     */
    protected $sitemapInc = 1;

    /**
     * @var int
     */
    protected $currentInc = 0;

    /**
     * @var bool
     */
    protected $init = false;

    /**
     * @var int
     */
    public $imageCount;

    /**
     * @var int
     */
    protected $videoCount;

    /**
     * @var string $storeBaseUrl
     */
    public $storeBaseUrl;

    /**
     * Writer constructor.
     *
     * @param Helper $helper
     * @param DateTime $date
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param File $io
     * @param ChangefreqValidator $changefreqValidator
     * @param LastmodValidator $lastmodValidator
     * @param LocationValidator $locationValidator
     * @param PriorityValidator $priorityValidator
     * @param LinkChecker $linkChecker
     * @throws FileSystemException
     */
    public function __construct(
        Helper $helper,
        DateTime $date,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        File $io,
        ChangefreqValidator $changefreqValidator,
        LastmodValidator $lastmodValidator,
        LocationValidator $locationValidator,
        PriorityValidator $priorityValidator,
        LinkChecker $linkChecker
    ) {
        $this->helper              = $helper;
        $this->date                = $date;
        $this->io                  = $io;
        $this->storeManager        = $storeManager;
        $this->directory           = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->changefreqValidator = $changefreqValidator;
        $this->lastmodValidator    = $lastmodValidator;
        $this->locationValidator   = $locationValidator;
        $this->priorityValidator   = $priorityValidator;
        $this->linkChecker         = $linkChecker;
    }

    /**
     * @param string $filePath
     * @param string $fileDir
     * @param string $fileName
     * @param string $tempFilePath
     * @param bool $storeBaseUrl
     * @param int $storeId
     * @param string $serverPath
     * @return mixed|void
     * @throws LocalizedException
     */
    public function init(
        $filePath,
        $fileDir,
        $fileName,
        $tempFilePath,
        $storeBaseUrl = false,
        $storeId = StoreModel::DEFAULT_STORE_ID,
        $serverPath = ''
    ) {
        $this->filePath     = $filePath;
        $this->fileDir      = $fileDir;
        $this->fileName     = $fileName;
        $this->tempFilePath = $tempFilePath;
        $this->imageCount   = 0;
        $this->videoCount   = 0;
        $this->sitemapInc   = 1;
        $this->currentInc   = 0;
        $this->serverPath   = $serverPath;

        $this->storeManager->setCurrentStore($storeId);

        $this->loadParamsFromConfig();

        if ($this->useIndex && !$storeBaseUrl) {
            throw new LocalizedException(
                __('The sitemap index file can\'t be created without storeBaseUrl . Process is canceled.')
            );
        } else {
            $this->storeBaseUrl = $storeBaseUrl;
        }

        $this->openXml();
        $this->init = true;
    }

    /**
     * Load params from config
     */
    protected function loadParamsFromConfig()
    {
        $splitSize = $this->helper->getSplitSize();
        if (!empty($splitSize)) {
            $this->splitSize = $splitSize;
        }

        $maxLinks = $this->helper->getMaxLinks();
        if (!empty($maxLinks)) {
            $this->maxLinks = $maxLinks;
        }
    }

    /**
     * @param string $rawUrl
     * @param string $lastmod
     * @param string $changefreq
     * @param string $priority
     * @param DataObject|false $imageUrls
     * @param DataObject|false $videoUrls
     * @return mixed|void
     * @throws LocalizedException
     */
    public function write($rawUrl, $lastmod, $changefreq, $priority, $imageUrls = false, $videoUrls = false)
    {
        if (!$this->init) {
            throw new LocalizedException(__('Sitemap Writer class wasn\'t initialized.'));
        }

        $url = htmlspecialchars($rawUrl);
        $this->isInputDataValid($url, $lastmod, $changefreq, $priority, $imageUrls);

        $countAdditionalLinks = 0;

        $imagePartXml = "";

        if ($imageUrls) {
            $imageCount           = count($imageUrls->getCollection());
            $countAdditionalLinks += $imageCount;
            $imagePartXml         .= $this->getImageXml($imageUrls);
        }

        $videoPartXml = "";

        if ($videoUrls) {
            $videoCount           = count($videoUrls->getCollection()) * 2; // + links to thumbnails
            $countAdditionalLinks += $videoCount;
            $videoPartXml         .= $this->getVideoXml($videoUrls);
        }

        $this->checkSitemapLimits($countAdditionalLinks);

        $xml = sprintf(
            '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority>%s%s</url>',
            $url,
            $lastmod,
            $changefreq,
            $priority,
            $imagePartXml,
            $videoPartXml
        );

        $this->stream->write($xml);
    }

    /**
     *
     * @param DataObject $images
     * @return string
     */
    protected function getImageXml(DataObject $images)
    {
        $xml = '';
        foreach ($images->getCollection() as $image) {

            $this->imageCount++;
            $preparedImageUrl     = htmlspecialchars($image->getUrl());
            $preparedThumbnailUrl = htmlspecialchars($images->getThumbnail());
            $preparedTitle        = htmlspecialchars($images->getTitle());
            $preparedCaption      = $image->getCaption() ? htmlspecialchars($image->getCaption()) : '';

            $xmlImage = $this->getWrappedString($preparedImageUrl, 'image:loc');
            $xmlImage .= $this->getWrappedString($preparedTitle, 'image:title');
            if ($preparedCaption) {
                $xmlImage .= $this->getWrappedString($preparedCaption, 'image:caption');
            }

            $xml .= $this->getWrappedString($xmlImage, 'image:image');
        }

        if ($xml) {
            $this->imageCount++;

            $xml .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0"><DataObject type="thumbnail">';
            $xml .= '<Attribute name="name" value="' . $preparedTitle . '"/>';
            $xml .= '<Attribute name="src" value="' . $preparedThumbnailUrl . '"/>';
            $xml .= '</DataObject></PageMap>';
        }

        return $xml;
    }

    /**
     * @param DataObject $videos
     * @return string
     */
    protected function getVideoXml(DataObject $videos)
    {
        $xml = '';
        foreach ($videos->getCollection() as $video) {

            $this->videoCount++;
            $preparedUrl          = htmlspecialchars($video->getVideo());
            $preparedThumbnailUrl = htmlspecialchars($video->getUrl());
            $preparedTitle        = '<![CDATA[' . $video->getVideoTitle() . ']]>';
            $preparedDescription  = '<![CDATA[' . $video->getVideoDescription() . ']]>';

            $xmlContents = $this->getWrappedString($preparedThumbnailUrl, 'video:thumbnail_loc');
            $xmlContents .= $this->getWrappedString($preparedTitle, 'video:title');
            $xmlContents .= $this->getWrappedString($preparedDescription, 'video:description');
            $xmlContents .= $this->getWrappedString($preparedUrl, 'video:content_loc');
            $xml         .= $this->getWrappedString($xmlContents, 'video:video');
        }

        return $xml;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getMediaUrl($url)
    {
        $storeBaseUrl = $this->getStoreBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return strripos($url, $storeBaseUrl) === false ? $storeBaseUrl . ltrim($url, '/') : $url;
    }

    /**
     * Get store base url
     *
     * @param string $type
     * @return string
     */
    protected function getStoreBaseUrl($type = UrlInterface::URL_TYPE_WEB)
    {
        /** @var StoreModel $store */
        $store = $this->storeManager->getStore();

        $isSecure = $store->isUrlSecure();

        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }

    /**
     *
     * @param string $string
     * @param string $tagName
     * @return string
     */
    protected function getWrappedString($string, $tagName)
    {
        return '<' . $tagName . '>' . $string . '</' . $tagName . '>';
    }

    /**
     * @param string $url
     * @param string $lastmod
     * @param string $changefreq
     * @param string $priority
     * @throws LocalizedException
     */
    protected function isInputDataValid($url, $lastmod, $changefreq, $priority)
    {
        if ($this->locationValidator->isValid($url) == false && $this->helper->isEnableValidateUrls()) {
            throw new LocalizedException(__("Location value '%1' is not valid.", $url));
        }

        if ($this->changefreqValidator->isValid($changefreq) == false) {
            throw new LocalizedException(__("Changefreq value '%1' is not valid. Item url: '%2'.", $changefreq, $url));
        }

        if ($this->lastmodValidator->isValid($lastmod) == false) {
            throw new LocalizedException(__("Lastmod value '%1' is not valid. Item url: '%2'.", $lastmod, $url));
        }

        if ($this->priorityValidator->isValid($priority) == false) {
            throw new LocalizedException(__("Priority value '%1' is not valid. Item url: '%2'.", $priority, $url));
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function openPathAndFileExist()
    {
        $filePath = $this->filePath;
        $fileName = $this->getSitemapFilename();

        $this->stream = $this->directory->openFile($fileName, 'a+');
    }

    /**
     * Write header
     */
    public function startWriteXml()
    {
        $this->openXml(true);
    }

    /**
     * Close file and generate index file
     */
    public function endWriteXml()
    {
        if ($this->init) {
            $this->closeXml();

            if ($this->sitemapInc == 1) {
                $path        = $this->filePath . $this->getSitemapFilename();
                $destination = $this->filePath . $this->fileName;

                $result = $this->io->mv($path, $destination);

                if (!$result) {
                    throw new LocalizedException(
                        __("The following file renaming from: file %1 into %2 is impossible.", $path, $destination)
                    );
                }
            } else {
                $this->generateSitemapIndex();
            }
        }
    }

    /**
     * @param bool $headerWrite
     */
    protected function openXml($headerWrite = false)
    {
        $this->openPathAndFileExist();
        $this->stream = $this->directory->openFile($this->getSitemapFilename(), 'w+');
        if ($headerWrite) {
            $this->writeXmlHeader();
        }
    }

    /**
     * Write header in xml file
     */
    protected function writeXmlHeader()
    {
        $this->stream->write(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        );

        $imageXmlSchema = "\n" . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $videoXmlSchema = "\n" . 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"';

        $this->stream->write(
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . ' ' . $imageXmlSchema . ' ' . $videoXmlSchema . '>'
        );
    }

    /**
     * @param int $countAdditionalLinks
     */
    protected function checkSitemapLimits($countAdditionalLinks = 0)
    {
        if ($this->useIndex) {
            if ($this->currentInc + $countAdditionalLinks >= $this->maxLinks) {
                $this->currentInc = 0;
                $this->closeXml();
                $this->sitemapInc++;
                $this->openXml(true);
            }

            $this->currentInc += 1 + $countAdditionalLinks;
        }
    }

    /**
     * @return string
     */
    protected function getSitemapFilename()
    {
        if ($this->useIndex) {
            $sitemapFilename = $this->fileName;
            $ext             = strrchr($sitemapFilename, '.');
            $sitemapFilename = substr($sitemapFilename, 0, strlen($sitemapFilename) - strlen($ext)) . '_' . sprintf(
                    '%03s',
                    $this->sitemapInc
                ) . $ext;

            return $sitemapFilename;
        }

        return trim($this->fileName, '/');
    }

    /**
     * close xml file
     */
    public function closeXml()
    {
        $this->stream = $this->directory->openFile($this->getSitemapFilename(), 'a+');
        $this->stream->write('</urlset>');
        $this->stream->close();

        $this->moveFileFromTempToOriginal();
    }

    /**
     * @param bool $fileName
     * @throws LocalizedException
     */
    protected function moveFileFromTempToOriginal($fileName = false)
    {
        if (!$fileName) {
            $fileName = $this->getSitemapFilename();
        }

        $from   = $this->tempFilePath . $fileName;
        $to     = $this->filePath . $fileName;
        $result = $this->io->mv($from, $to);
        if (!$result) {
            throw new LocalizedException(__("Relocation of the file %1 to %2 is impossible.", $from, $to));
        }
    }

    /**
     * generate indexfile
     */
    protected function generateSitemapIndex()
    {
        if (!$this->useIndex) {
            return;
        }

        $this->openPathAndFileExist();

        $this->stream = $this->directory->openFile($this->fileName, 'w+');
        $this->stream->write('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $this->stream->write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        $date = $this->date->gmtDate('Y-m-d');
        $i    = $this->sitemapInc;

        for ($this->sitemapInc = 1; $this->sitemapInc <= $i; $this->sitemapInc++) {
            $fileName = $this->getSitemapFilename();
            $urlPart  = ltrim(str_replace(trim($this->serverPath, '/') . '/', '/', $this->fileDir), '/');

            $urls['store_url'] = $this->getStoreBaseUrl() . $urlPart . $fileName;

            $store = $this->storeManager->getStore();
            if ($store->isUseStoreInUrl()) {
                $urls['no_store_code_url'] = str_replace($store->getCode() . '/', '', $urls['store_url']);
            }

            $urls['base_url'] = $this->getStoreBaseUrl() . ltrim($this->fileDir, '/') . $fileName;

            if ($this->helper->isCheckUrlsAvailability() && empty($validKey)) {
                $validKey = $this->linkChecker->checkUrls($urls, $store->getId());
            }

            if (empty($validKey)) {
                $validKey = 'base_url';
            }

            $url = $urls[$validKey];

            $xml = sprintf(
                '<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>',
                htmlspecialchars($url),
                $date
            );
            $this->stream->write($xml);
        }

        $this->sitemapInc = $i;

        $this->stream->write('</sitemapindex>');
        $this->stream->close();

        $this->moveFileFromTempToOriginal($this->fileName);
    }

}