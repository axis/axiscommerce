<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_2_5 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.5';
    protected $_info = 'Price indexes';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_price_index')}`;
        CREATE TABLE `{$installer->getTable('catalog_product_price_index')}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `product_id` int(10) unsigned NOT NULL,
          `site_id` smallint(5) unsigned NOT NULL,
          `customer_group_id` smallint(5) unsigned NOT NULL,
          `time_from` int(10) unsigned NOT NULL,
          `time_to` int(10) unsigned NOT NULL,
          `min_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
          `max_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
          `final_min_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
          `final_max_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
          PRIMARY KEY (`id`),
          KEY `FK_CATALOG_PRODUCT_PRICE_INDEX_PRODUCT_ID` (`product_id`),
          KEY `FK_CATALOG_PRODUCT_PRICE_INDEX_SITE_ID` (`site_id`),
          KEY `FK_CATALOG_RPODUCT_PRICE_INDEX_CUSTOMER_GROUP_ID` (`customer_group_id`),
          KEY `IDX_TIME_FROM` USING BTREE (`time_from`),
          KEY `IDX_TIME_TO` USING BTREE (`time_to`),
          CONSTRAINT `FK_CATALOG_PRODUCT_PRICE_INDEX_PRODUCT_ID` FOREIGN KEY (`product_id`)
            REFERENCES `{$installer->getTable('catalog_product')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_PRODUCT_PRICE_INDEX_SITE_ID` FOREIGN KEY (`site_id`)
            REFERENCES `{$installer->getTable('core_site')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_RPODUCT_PRICE_INDEX_CUSTOMER_GROUP_ID` FOREIGN KEY (`customer_group_id`)
            REFERENCES `{$installer->getTable('account_customer_group')}` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        ");

        Axis::single('admin/acl_resource')
            ->add("admin/catalog_index/update-price-index");
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_price_index')}`;

        ");

        Axis::single('admin/acl_resource')
            ->remove("admin/catalog_index/update-price-index");
    }
}