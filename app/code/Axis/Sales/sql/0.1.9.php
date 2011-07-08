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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sales_Upgrade_0_1_9 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.9';
    protected $_info = 'phone fields can be null';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order')}`
            MODIFY COLUMN `delivery_phone` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            MODIFY COLUMN `billing_phone` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL;

        ");
    }

    public function down()
    {

    }
}