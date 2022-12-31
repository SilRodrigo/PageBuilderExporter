<?php

declare(strict_types=1);

namespace Rsilva\PagebuilderExporter\Plugin\Adminhtml;

use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\PageBuilder\Ui\Component\Listing\Columns\TemplateManagerActions as Actions;
use Rsilva\PagebuilderExporter\Controller\Adminhtml\Template\Export;

class TemplateManagerActions
{

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
    }

    public function afterPrepareDataSource(Actions $templateManagerActions, $result)
    {

        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as &$item) {
                $name = $templateManagerActions->getData('name');
                $indexField = $templateManagerActions->getData('config/indexField') ?: 'template_id';
                if (isset($item[$indexField])) {                    
                    $item[$name]['export'] = [
                        'label' => __('Export'),
                        'href' => $this->urlBuilder->getUrl(
                            Export::URL,
                            [
                                'template_id' => $item[$indexField],
                            ]
                        )
                    ];
                }
            }
        }

        return $result;
    }
}
