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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sales_Upgrade_0_2_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.4';
    protected $_info = 'Increased size of product_option_value column';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order_product_attribute')}`
            MODIFY COLUMN `product_option_value` TEXT NOT NULL default '';

        ");
    }
}
