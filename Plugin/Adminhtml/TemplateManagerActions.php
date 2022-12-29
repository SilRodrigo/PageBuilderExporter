<?php

declare(strict_types=1);

namespace Rsilva\PagebuilderExporter\Plugin\Adminhtml;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\PageBuilder\Model\Stage\Config;
use Magento\PageBuilder\Ui\Component\Listing\Columns\TemplateManagerActions as Actions;

class TemplateManagerActions
{

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        Escaper $escaper,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization
    )
    {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
    }

    public function afterPrepareDataSource(Actions $templateManagerActions, $result)
    {

        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as & $item) {
                $name = $templateManagerActions->getData('name');
                $indexField = $templateManagerActions->getData('config/indexField') ?: 'template_id';
                if (isset($item[$indexField])) {
                    $templateName = $this->escaper->escapeHtml($item['name']);
                    if ($this->authorization->isAllowed(Config::TEMPLATE_DELETE_RESOURCE)) {
                        $item[$name]['export'] = [
                            'label' => __('Export'),
                            'href' => $this->urlBuilder->getUrl(
                                'pagebuilder/template/export',
                                [
                                    'template_id' => $item[$indexField],
                                ]
                            )
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
