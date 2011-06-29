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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_2_6 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.6';
    protected $_info = 'Mysql indexes added to fields used for sort order';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        ALTER TABLE `{$installer->getTable('catalog_product_option_text')}`
            ADD INDEX `IDX_CATALOG_PRODUCT_OPTION_TEXT_NAME`(`name`);

        ALTER TABLE `{$installer->getTable('catalog_product_option_value')}`
            ADD INDEX `IDX_CATALOG_PRODUCT_OPTION_VALUE_SORT_ORDER`(`sort_order`);

        ALTER TABLE `{$installer->getTable('catalog_product_option_value_text')}`
            ADD INDEX `IDX_CATALOG_PRODUCT_OPTION_VALUE_TEXT_NAME`(`name`);

        ");
    }

    public function down()
    {

    }
}