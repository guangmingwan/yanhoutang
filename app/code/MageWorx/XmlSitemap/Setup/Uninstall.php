<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class Uninstall implements UninstallInterface
{

    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $categorySetupManager = $this->categorySetupFactory->create();
        $categorySetupManager->removeAttribute(Product::ENTITY, 'in_xml_sitemap');
        $categorySetupManager->removeAttribute(Category::ENTITY, 'in_xml_sitemap');

        $connection = $setup->getConnection();
        $connection->dropColumn($connection->getTableName('cms_page'), 'in_xml_sitemap');
        $connection->dropColumn($connection->getTableName('sitemap'), 'count_by_entity');
        $connection->dropColumn($connection->getTableName('sitemap'), 'entity_type');
        $connection->dropColumn($connection->getTableName('sitemap'), 'server_path');
        $connection->dropColumn($connection->getTableName('sitemap'), 'sitemap_link');

        $setup->endSetup();
    }
}
