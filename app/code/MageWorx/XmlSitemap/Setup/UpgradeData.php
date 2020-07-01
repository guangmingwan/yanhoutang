<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Setup;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Store\Model\Store;
use MageWorx\SeoAll\Helper\LinkFieldResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\Collection;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;
use Zend_Db_Select;


/**
 * Upgrade Data script
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var LinkFieldResolver
     */
    protected $linkFieldResolver;

    /**
     * @var CollectionFactory
     */
    protected $sitemapCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Default value for "in_xml_sitemap" attribute
     */
    const IN_SITEMAP_XML_DEFAULT_VALUE = 1;

    /**
     * UpgradeData constructor.
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param LinkFieldResolver $linkFieldResolver
     * @param CollectionFactory $sitemapCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        LinkFieldResolver $linkFieldResolver,
        CollectionFactory $sitemapCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->_eavAttribute            = $eavAttribute;
        $this->categorySetupFactory     = $categorySetupFactory;
        $this->linkFieldResolver        = $linkFieldResolver;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->logger                   = $logger;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $attributeTableFieldsProduct  = $setup->getConnection()->describeTable(
            $setup->getTable('catalog_product_entity_int')
        );
        $attributeTableFieldsCategory = $setup->getConnection()->describeTable(
            $setup->getTable('catalog_category_entity_int')
        );


        /** @var CategorySetup $catalogSetup */
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $catalogSetup  = $this->categorySetupFactory->create(['setup' => $setup]);
            $attributeCode = 'in_xml_sitemap';

            $catalogSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                [
                    'group'            => 'Search Engine Optimization',
                    'type'             => 'int',
                    'backend'          => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
                    'frontend'         => '',
                    'label'            => 'Include in XML Sitemap',
                    'input'            => 'select',
                    'class'            => '',
                    'source'           => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global'           => Attribute::SCOPE_STORE,
                    'visible'          => true,
                    'required'         => false,
                    'user_defined'     => false,
                    'default'          => self::IN_SITEMAP_XML_DEFAULT_VALUE,
                    'apply_to'         => '',
                    'visible_on_front' => false,
                    'note'             => 'This setting was added by MageWorx XML Sitemap'
                ]
            );

            $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

            $productTypeId          = $catalogSetup->getEntityTypeId(Product::ENTITY);
            $selectProductAttribute = $setup->getConnection()->select();

            $selectProductAttribute
                ->from(
                    ['ea' => $setup->getTable('eav_attribute')],
                    ['attribute_id']
                )
                ->where("`entity_type_id` = '" . $productTypeId . "'")
                ->where("attribute_code = ?", $attributeCode);

            $linkField = $this->linkFieldResolver->getLinkField(ProductInterface::class, 'entity_id');

            $productAttributeId = $setup->getConnection()->fetchOne($selectProductAttribute);
            if (is_numeric($productAttributeId)) {
                $productAttributeValueInsert = $setup->getConnection()->select()->from(
                    ['e1' => $setup->getTable('catalog_product_entity')],
                    array_merge(
                        $attributeTableFieldsProduct,
                        [
                            'value_id'     => new Zend_Db_Expr('NULL'),
                            'attribute_id' => new Zend_Db_Expr($productAttributeId),
                            'store_id'     => new Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                            $linkField     => 'e1.' . $linkField,
                            'value'        => new Zend_Db_Expr(self::IN_SITEMAP_XML_DEFAULT_VALUE),
                        ]
                    )
                )
                                                     ->where(
                                                         'e1.' . $linkField . ' NOT IN(' . new Zend_Db_Expr(
                                                             "SELECT `" . $linkField . "` FROM " . $setup->getTable(
                                                                 'catalog_product_entity_int'
                                                             ) .
                                                             " WHERE `store_id` = 0 AND `attribute_id` = " . $productAttributeId . ")"
                                                         )
                                                     )
                                                     ->order(['e1.' . $linkField], Zend_Db_Select::SQL_ASC)
                                                     ->insertFromSelect(
                                                         $setup->getTable('catalog_product_entity_int')
                                                     );
                $setup->run($productAttributeValueInsert);
            }

            $catalogSetup->addAttribute(
                Category::ENTITY,
                $attributeCode,
                [
                    'group'            => 'Search Engine Optimization',
                    'type'             => 'int',
                    'backend'          => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
                    'frontend'         => '',
                    'label'            => 'Include in XML Sitemap',
                    'input'            => 'select',
                    'class'            => '',
                    'source'           => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global'           => Attribute::SCOPE_STORE,
                    'visible'          => true,
                    'required'         => false,
                    'user_defined'     => false,
                    'default'          => self::IN_SITEMAP_XML_DEFAULT_VALUE,
                    'apply_to'         => '',
                    'visible_on_front' => false,
                    'sort_order'       => 10,
                    'note'             => 'This setting was added by MageWorx XML Sitemap'
                ]
            );
            $categoryTypeId          = $catalogSetup->getEntityTypeId(Category::ENTITY);
            $selectCategoryAttribute = $setup->getConnection()->select();

            $selectCategoryAttribute
                ->from(
                    ['ea' => $setup->getTable('eav_attribute')],
                    ['attribute_id']
                )
                ->where("`entity_type_id` = '" . $categoryTypeId . "'")
                ->where("attribute_code = ?", $attributeCode);

            $categoryLinkField   = $this->linkFieldResolver->getLinkField(CategoryInterface::class, 'entity_id');
            $categoryAttributeId = $setup->getConnection()->fetchOne($selectCategoryAttribute);

            if (is_numeric($categoryAttributeId)) {
                $itemsInsert = $setup->getConnection()->select()->from(
                    ['e1' => $setup->getTable('catalog_category_entity')],
                    array_merge(
                        $attributeTableFieldsCategory,
                        [
                            'value_id'         => new Zend_Db_Expr('NULL'),
                            'attribute_id'     => new Zend_Db_Expr($categoryAttributeId),
                            'store_id'         => new Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                            $categoryLinkField => 'e1.' . $categoryLinkField,
                            'value'            => new Zend_Db_Expr(self::IN_SITEMAP_XML_DEFAULT_VALUE),
                        ]
                    )
                )
                                     ->where(
                                         'e1.' . $categoryLinkField . ' NOT IN(' . new Zend_Db_Expr(
                                             "SELECT `" . $categoryLinkField . "` FROM " . $setup->getTable(
                                                 'catalog_category_entity_int'
                                             ) .
                                             " WHERE `store_id` = 0 AND `attribute_id` = " . $categoryAttributeId . ")"
                                         )
                                     )
                                     ->order(['e1.' . $categoryLinkField], Zend_Db_Select::SQL_ASC)
                                     ->insertFromSelect(
                                         $setup->getTable('catalog_category_entity_int')
                                     );
                $setup->run($itemsInsert);
            }
        }

        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $this->updateSitemapPaths();
        }

        $setup->endSetup();
    }

    /**
     * @return void
     */
    protected function updateSitemapPaths()
    {
        try {
            /** @var Collection $sitemapCollection */
            $sitemapCollection = $this->sitemapCollectionFactory->create();

            //convert paths on model saving
            $sitemapCollection->save();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}