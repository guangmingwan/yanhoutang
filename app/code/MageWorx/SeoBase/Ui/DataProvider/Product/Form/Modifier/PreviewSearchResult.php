<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Exception\NoSuchEntityException;

class PreviewSearchResult extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PreviewSearchResult constructor.
     *
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(LocatorInterface $locator, StoreManagerInterface $storeManager)
    {
        $this->locator      = $locator;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $meta
     * @return array
     * @throws NoSuchEntityException
     */
    public function modifyMeta(array $meta)
    {
        if (isset($meta['search-engine-optimization']['children'])) {

            $meta = array_replace_recursive(
                $meta,
                [
                    'search-engine-optimization' => [
                        'children' => [
                            'container_url_key'               => [
                                'children' => [
                                    'url_key' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'valueUpdate' => 'keyup'
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'container_meta_title'            => [
                                'children' => [
                                    'meta_title' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'valueUpdate' => 'keyup'
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'container_meta_description'      => [
                                'children' => [
                                    'meta_description' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'valueUpdate' => 'keyup'
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'container_preview_search_result' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'formElement'   => 'container',
                                            'componentType' => 'container',
                                            'sortOrder'     => 0,
                                        ],
                                    ],
                                ],
                                'children'  => [
                                    'preview_url'         => $this->getPreviewUrlConfig(10),
                                    'preview_title'       => $this->getPreviewTitleConfig(20),
                                    'preview_description' => $this->getPreviewDescriptionConfig(30),
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        return $meta;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param int $sortOrder
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getPreviewUrlConfig($sortOrder)
    {
        $storeId = $this->getStoreId();

        if ($storeId === Store::DEFAULT_STORE_ID) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'elementTmpl'       => 'ui/form/element/text',
                        'component'         => 'MageWorx_SeoBase/js/form/element/preview-url',
                        'imports'           => [
                            'prepareValue' => '${ $.provider }:data.product.url_key'
                        ],
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'preview preview_url',
                        'baseUrl'           => $this->storeManager->getStore($storeId)->getBaseUrl(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getPreviewTitleConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'elementTmpl'       => 'ui/form/element/text',
                        'component'         => 'MageWorx_SeoBase/js/form/element/preview-title',
                        'imports'           => [
                            'prepareValue' => '${ $.provider }:data.product.meta_title'
                        ],
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'preview preview_title'
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getPreviewDescriptionConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'elementTmpl'       => 'ui/form/element/text',
                        'component'         => 'MageWorx_SeoBase/js/form/element/preview-description',
                        'imports'           => [
                            'prepareValue' => '${ $.provider }:data.product.meta_description'
                        ],
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'preview preview_description'
                    ],
                ],
            ],
        ];
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->locator->getStore()->getId();
    }
}
