<?php

/**
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_PabeBuilderExporter
 */

declare(strict_types=1);

namespace Rsilva\PageBuilderExporter\Model\Template;

use Magento\Framework\App\Response\Http\FileFactory;
use Magento\PageBuilder\Model\TemplateRepository;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Model\Template;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Controller that download file by name.
 */
class Export
{
    /**
     * Url to this controller
     */
    public const URL = 'pagebuilder_exporter/template/export/';
    public const CONTENT_TYPE = 'application/json';
    public const TEMPLATE_NOT_EXIST = 'Template does not exist!';
    public const TEMPLATE_IMAGE_DATA = 'image_base_data';

    /**
     * @var FileFactory
     */
    private $_fileFactory;

    /**
     * @var TemplateRepository
     */
    private $_templateRepository;

    /**
     * @var TimezoneInterface
     */
    private $_date;

    /**
     * @var WriteInterface
     */
    private $_varDirectory;

    /**
     * @var WriteInterface
     */
    private $_mediaDirectory;

    /**
     * @var File
     */
    private $_driverFile;

    /**
     * ExportFile constructor.
     * @param FileFactory $fileFactory
     * @param TemplateRepository $templateRepository
     * @param TimezoneInterface $date
     * @param Filesystem $filesystem
     * @param File $driverFile
     */
    public function __construct(
        FileFactory $fileFactory,
        TemplateRepository $templateRepository,
        TimezoneInterface $date,
        Filesystem $filesystem,
        File $driverFile
    ) {
        $this->_fileFactory = $fileFactory;
        $this->_templateRepository = $templateRepository;
        $this->_date =  $date;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_EXPORT);
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_driverFile = $driverFile;
    }

    protected function generateFileName(string $prefix)
    {
        return $prefix . '_' . $this->_date->date()->format('Y-m-d') . '.json';
    }

    /**
     * Returns a json file
     *
     * @param string $fileName;
     * @param string $content;
     * @return Magento\Framework\App\ResponseInterface
     */
    protected function generateJsonFile(string $fileName, string $content)
    {
        $this->_varDirectory->writeFile($fileName, $content);
        return $this->_fileFactory->create(
            $fileName,
            [
                'type'  => "filename",
                'value' => $fileName,
                'rm'    => true,
            ],
            DirectoryList::VAR_EXPORT,
            Export::CONTENT_TYPE
        );
    }

    /**
     * Returns a base64 image data
     *
     * @param string $fileName;
     * @return string
     */
    protected function getBaseData(string $fileName): string
    {
        try {
            $content =  $this->_driverFile->fileGetContents($this->_mediaDirectory->getAbsolutePath() . $fileName);
            return 'data:image/jpeg;base64,' . base64_encode($content);
        } catch (\Throwable $th) {
            return '';
        }
    }

    /**
     * Generate a json file with a pagebuilder template
     * @param string $templateId
     * @return Magento\Framework\App\ResponseInterface
     */
    public function execute($templateId)
    {
        /** @var Template $template */
        $template = $this->_templateRepository->get($templateId);
        if (!$template->getId()) throw new NoSuchEntityException(__(Export::TEMPLATE_NOT_EXIST));
        $fileName = $this->generateFileName('pagebuilder-' . $template->getName());
        $content = json_encode(
            [
                TemplateInterface::KEY_NAME => $template->getName(),
                TemplateInterface::KEY_CREATED_FOR => $template->getCreatedFor(),
                TemplateInterface::KEY_TEMPLATE => $template->getTemplate(),
                TemplateInterface::KEY_PREVIEW_IMAGE => $template->getPreviewImage(),
                Export::TEMPLATE_IMAGE_DATA => $this->getBaseData($template->getPreviewImage())
            ]
        );
        return $this->generateJsonFile($fileName, $content);
    }
}
