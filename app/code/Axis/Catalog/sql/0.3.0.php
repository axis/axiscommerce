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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_3_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.3.0';
    protected $_info = 'Related products added';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_related')}`;
        CREATE TABLE  `{$installer->getTable('catalog_product_related')}` (
          `product_id` int(10) unsigned NOT NULL,
          `related_product_id` int(10) unsigned NOT NULL,
          `sort_order` tinyint unsigned NOT NULL DEFAULT '50',
          PRIMARY KEY (`product_id`,`related_product_id`),
          KEY `FK_CATALOG_PRODUCT_RELATED_RELATED_PRODUCT_ID` (`related_product_id`),
          KEY `IDX_CATALOG_PRODUCT_RELATED_SORT_ORDER` (`sort_order`),
          CONSTRAINT `FK_CATALOG_PRODUCT_RELATED_PRODUCT_ID` FOREIGN KEY (`product_id`)
            REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_CATALOG_PRODUCT_RELATED_RELATED_PRODUCT_ID` FOREIGN KEY (`related_product_id`)
            REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ");
    }
}
