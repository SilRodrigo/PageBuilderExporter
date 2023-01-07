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
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Rsilva\PageBuilderExporter\Model\Template\Export as TemplateExport;

/**
 * Controller that exports pagebuilder template.
 */
class Export extends ExportController implements HttpGetActionInterface
{
    /**
     * @var TemplateExport
     */
    private $_templateExport;

    /**
     * ExportFile constructor.
     * @param Action\Context $context
     * @param TemplateExport $templateExport
     */
    public function __construct(
        Action\Context $context,
        TemplateExport $templateExport
    ) {
        parent::__construct($context);
        $this->_templateExport = $templateExport;
    }

    /**
     * @return Magento\Framework\App\ResponseInterface|Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            return $this->_templateExport->execute($this->getRequest()->getParam('template_id'));
        } catch (\Throwable $th) {
            $this->messageManager->addErrorMessage($th->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('pagebuilder/template/index');
            return $resultRedirect;
        }
    }
}
