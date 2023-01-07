<?php

/**
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_PabeBuilderExporter
 */

declare(strict_types=1);

namespace Rsilva\PageBuilderExporter\Model\Template;

use Magento\PageBuilder\Model\TemplateRepository;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\Framework\Filesystem\Driver\File;
use Rsilva\PageBuilderExporter\Model\Template\Export;
use Magento\Framework\Filesystem;
use Magento\Framework\Api\ImageContentFactory;
use Magento\Framework\Api\ImageContent;
use Magento\Framework\Api\ImageContentValidator;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;

class Import
{
    /**
     * @var TemplateFactory
     */
    private $_templateFactory;

    /**
     * @var ImageContentFactory
     */
    private $_imageContentFactory;

    /**
     * @var TemplateRepository
     */
    private $_templateRepository;

    /**
     * @var File
     */
    private $_driverFile;

    /**
     * @var Filesystem
     */
    private $_filesystem;

    /**
     * @var ImageContentValidator
     */
    private $_imageContentValidator;

    /**
     * @var AdapterFactory
     */
    private $_imageAdapterFactory;

    /**
     * @var Database
     */
    private $_mediaStorage;

    /**
     * @param TemplateFactory $templateFactory
     * @param ImageContentFactory $imageContentFactory
     * @param TemplateRepository $templateRepository
     * @param File $driverFile
     * @param Filesystem $filesystem
     * @param ImageContentValidator $imageContentValidator
     * @param AdapterFactory $imageAdapterFactory
     * @param Database $mediaStorage
     */
    public function __construct(
        TemplateFactory $templateFactory,
        ImageContentFactory $imageContentFactory,
        TemplateRepository $templateRepository,
        File $driverFile,
        Filesystem $filesystem,
        ImageContentValidator $imageContentValidator,
        AdapterFactory $imageAdapterFactory,
        Database $mediaStorage
    ) {
        $this->_templateFactory = $templateFactory;
        $this->_imageContentFactory = $imageContentFactory;
        $this->_templateRepository = $templateRepository;
        $this->_driverFile = $driverFile;
        $this->_filesystem = $filesystem;
        $this->_imageContentValidator = $imageContentValidator;
        $this->_imageAdapterFactory = $imageAdapterFactory;
        $this->_mediaStorage = $mediaStorage;
    }

    protected function getFileContent($path)
    {
        $content =  $this->_driverFile->fileGetContents($path);
        return json_decode($content, true);
    }

    protected function storePreviewImage($baseImageData, $imageName)
    {
        list($type, $baseImageData) = explode(';', $baseImageData);
        list(, $baseImageData)      = explode(',', $baseImageData);
        $decodedImage = base64_decode($baseImageData);
        /** @var ImageContent $imageContent */
        $imageContent = $this->_imageContentFactory->create();
        $imageContent->setBase64EncodedData($baseImageData);
        $imageContent->setName($imageName);
        $imageContent->setType(str_replace('data:', '', $type));

        if ($this->_imageContentValidator->isValid($imageContent)) {
            $mediaDirWrite = $this->_filesystem
                ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $directory = $mediaDirWrite->getAbsolutePath();
            $mediaDirWrite->create($directory);
            $fileAbsolutePath = $directory . $imageName;
            // Write the file to the directory
            $mediaDirWrite->getDriver()->filePutContents($fileAbsolutePath, $decodedImage);
            // Generate a thumbnail, called -thumb next to the image for usage in the grid
            $thumbPath = str_replace('.jpg', '-thumb.jpg', $imageName);
            $thumbAbsolutePath = $directory . $thumbPath;
            $imageFactory = $this->_imageAdapterFactory->create();
            $imageFactory->open($fileAbsolutePath);
            $imageFactory->resize(350);
            $imageFactory->save($thumbAbsolutePath);
            $this->_mediaStorage->saveFile($fileAbsolutePath);
            $this->_mediaStorage->saveFile($thumbAbsolutePath);
        }
    }

    protected function createTemplate($templateData): TemplateInterface
    {
        /** @var TemplateInterface $template */
        $template = $this->_templateFactory->create();
        $template->setName($templateData[TemplateInterface::KEY_NAME]);
        $template->setCreatedFor($templateData[TemplateInterface::KEY_CREATED_FOR]);
        $template->setTemplate($templateData[TemplateInterface::KEY_TEMPLATE]);
        $template->setPreviewImage($templateData[TemplateInterface::KEY_PREVIEW_IMAGE]);
        if ($templateData[Export::TEMPLATE_IMAGE_DATA]) {
            $this->storePreviewImage($templateData[Export::TEMPLATE_IMAGE_DATA], $template->getPreviewImage());
        }
        return $template;
    }

    public function execute($file)
    {
        $fileTmp = $file['tmp_name'];
        $templateData = $this->getFileContent($fileTmp);
        $template = $this->createTemplate($templateData);
        $this->_templateRepository->save($template);
    }
}
