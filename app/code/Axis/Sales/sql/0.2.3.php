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

class Axis_Sales_Upgrade_0_2_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.3';
    protected $_info = 'Increased size of shipping and payment method columns';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order')}`
            MODIFY COLUMN `payment_method` VARCHAR(255) NOT NULL default '',
            MODIFY COLUMN `payment_method_code` VARCHAR(255) NOT NULL default '',
            MODIFY COLUMN `shipping_method` VARCHAR(255) NOT NULL default '',
            MODIFY COLUMN `shipping_method_code` VARCHAR(255) NOT NULL default '';

        ");
    }
}
