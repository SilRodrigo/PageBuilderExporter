<?php

/**
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_PabeBuilderExporter
 */

declare(strict_types=1);

namespace Rsilva\PageBuilderExporter\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Rsilva\PageBuilderExporter\Model\Template\Import as TemplateImport;

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
     * @var TemplateImport
     */
    private $_templateImport;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TemplateImport $templateImport
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TemplateImport $templateImport
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_templateImport = $templateImport;
    }

    public function execute()
    {
        $jsonFile = $this->getRequest()->getFiles();
        if (!empty($jsonFile['template_json_file'])) {
            try {
                $jsonFile = $jsonFile['template_json_file'];
                $fileName = $jsonFile['name'];
                $this->_templateImport->execute($jsonFile);
                $this->messageManager->addSuccessMessage(__(Import::IMPORT_SUCCESS_MESSAGE, $fileName));
                $result_redirect = $this->resultRedirectFactory->create();
                $result_redirect->setPath('pagebuilder/template/index');
                return $result_redirect;
            } catch (\Throwable $th) {
                $this->messageManager->addErrorMessage(Import::IMPORT_ERROR, $fileName);
                $this->messageManager->addErrorMessage($th->getMessage());
            }
        }
        $result_page = $this->_resultPageFactory->create();
        $result_page->getConfig()->getTitle()->prepend(__('Import PageBuilder Template'));
        return $result_page;
    }
}
