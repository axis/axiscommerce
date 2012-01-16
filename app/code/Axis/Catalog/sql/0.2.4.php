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

class Axis_Catalog_Upgrade_0_2_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.4';
    protected $_info = 'Description for manufacturer';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('catalog_product_manufacturer_title')}`
            RENAME TO `{$installer->getTable('catalog_product_manufacturer_description')}`;

        ALTER TABLE `{$installer->getTable('catalog_product_manufacturer_description')}`
            MODIFY COLUMN `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
            ADD COLUMN `description` TEXT DEFAULT '' AFTER `title`;

        ");
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('catalog_product_manufacturer_description')}`
            DROP COLUMN `description`;

        ALTER TABLE `{$installer->getTable('catalog_product_manufacturer_description')}`
            RENAME TO `{$installer->getTable('catalog_product_manufacturer_title')}`;

        ");
    }
}