<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Rsilva\PagebuilderExporter\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageBuilder\Model\Config;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\PageBuilder\Api\TemplateRepositoryInterface;

/**
 * Controller that download file by name.
 */
class Import extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $_resultPageFactory;

    /**
     * @var Config
     */
    private $_config;

    /**
     * @var TemplateRepositoryInterface
     */
    private $_templateRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Config $config,
        TemplateRepositoryInterface $templateRepository
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_config = $config;
        $this->_templateRepository = $templateRepository;
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import PageBuilder Template'));
        return $resultPage;
    }
}
