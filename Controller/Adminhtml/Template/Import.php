<?php

/**
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_PabeBuilderExporter
 */

declare(strict_types=1);

namespace Rsilva\PageBuilderExporter\Controller\Adminhtml\Template;

use Rsilva\PagebuilderExporter\Api\ImportExportConfigInterface as ConfigInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageBuilder\Model\TemplateRepository;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Controller that import pagebuilder template.
 */
class Import extends Action implements ActionInterface
{
    public const IMPORT_SUCCESS_MESSAGE = '"%1" template file was imported with success!';
    public const IMPORT_ERROR = 'There was an error importing %1, please try again.';

    /**
     * @var PageFactory
     */
    private $_resultPageFactory;

    /**
     * @var TemplateFactory
     */
    private $_templateFactory;

    /**
     * @var TemplateRepository
     */
    private $_templateRepository;

    /**
     * @var File
     */
    private $_driverFile;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     * @param File $driverFile
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository,
        File $driverFile
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_templateFactory = $templateFactory;
        $this->_templateRepository = $templateRepository;
        $this->_driverFile = $driverFile;
    }

    public function execute()
    {
        $json_file = $this->getRequest()->getFiles();
        if (!empty($json_file['template_json_file'])) {
            $json_file = $json_file['template_json_file'];
            $file_name = $json_file['name'];
            try {
                $content =  $this->_driverFile->fileGetContents($json_file['tmp_name']);
                $import_template = json_decode($content, true);
                /** @var TemplateInterface $template */
                $template = $this->_templateFactory->create();
                $template->setName($import_template[TemplateInterface::KEY_NAME]);
                $template->setCreatedFor($import_template[TemplateInterface::KEY_CREATED_FOR]);
                $template->setTemplate($import_template[TemplateInterface::KEY_TEMPLATE]);
                $template->setPreviewImage('blank.png');
                $this->_templateRepository->save($template);
                $this->messageManager->addSuccessMessage(__(Import::IMPORT_SUCCESS_MESSAGE, $file_name));
                $result_redirect = $this->resultRedirectFactory->create();
                $result_redirect->setPath('pagebuilder/template/index');
                return $result_redirect;
            } catch (\Throwable $th) {
                $this->messageManager->addErrorMessage(Import::IMPORT_ERROR, $file_name);
                $this->messageManager->addErrorMessage($th->getMessage());
            }
        }
        $result_page = $this->_resultPageFactory->create();
        $result_page->getConfig()->getTitle()->prepend(__('Import PageBuilder Template'));
        return $result_page;
    }
}
