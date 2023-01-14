<?php

/**
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_PabeBuilderExporter
 */

namespace Rsilva\PagebuilderExporter\Block\Form;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back;
use Magento\Backend\Block\Widget\Button;

class Import extends \Magento\Backend\Block\Widget\Form
{

    /**
     *  @var Back
     */
    private $_backButton;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Back $backButton,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_backButton = $backButton;
    }

    /**
     * Prepares layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        /** @var Magento\Framework\View\Element\BlockInterface $toolbar */
        $toolbar = $this->getToolbar();
        $toolbar->addChild(
            'back_button',
            Button::class,
            $this->_backButton->getButtonData()
        );
        $toolbar->addChild(
            'save_button',
            Button::class,
            [
                'label' => __('Import'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#import_template_json_form']],
                ]
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Returns URL for save action
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('pagebuilder/*/import');
    }
}
