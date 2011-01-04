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
 * @package     Axis_Sales
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sales_Upgrade_0_1_8 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.8';
    protected $_info = 'Customer order comments';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order')}`
            ADD COLUMN `customer_comment` TEXT AFTER `site_id`,
            ADD COLUMN `admin_comment` TEXT AFTER `customer_comment`;

        ");
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order')}`
            DROP COLUMN `customer_comment`,
            DROP COLUMN `admin_comment`;

        ");
    }
}