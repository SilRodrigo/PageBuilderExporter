<?php

/**
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_PabeBuilderExporter
 */

declare(strict_types=1);

namespace Rsilva\PageBuilderExporter\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\PageBuilder\Model\TemplateRepository;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Model\Template;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Controller that download file by name.
 */
class Export extends ExportController implements HttpGetActionInterface
{
    /**
     * Url to this controller
     */
    public const URL = 'pagebuilder_exporter/template/export/';
    public const CONTENT_TYPE = 'application/json';
    public const TEMPLATE_NOT_EXIST = 'Template does not exist!';

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
     * ExportFile constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param TemplateRepository $templateRepository
     * @param TimezoneInterface $date
     * @param Filesystem $filesystem
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        TemplateRepository $templateRepository,
        TimezoneInterface $date,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->_templateRepository = $templateRepository;
        $this->_date =  $date;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_EXPORT);
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
     * @return Magento\Framework\App\ResponseInterface|Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            /** @var Template $template */
            $template = $this->_templateRepository->get($this->getRequest()->getParam('template_id'));
            if (!$template->getId()) {
                throw new NoSuchEntityException(__(Export::TEMPLATE_NOT_EXIST));
            }
            $fileName = $this->generateFileName('pagebuilder-' . $template->getName());
            $content = json_encode(
                [
                    TemplateInterface::KEY_NAME => $template->getName(),
                    TemplateInterface::KEY_CREATED_FOR => $template->getCreatedFor(),
                    TemplateInterface::KEY_TEMPLATE => $template->getTemplate(),
                ]
            );
            return $this->generateJsonFile($fileName, $content);
        } catch (\Throwable $th) {
            $this->messageManager->addErrorMessage($th->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('pagebuilder/template/index');
            return $resultRedirect;
        }
    }
}
