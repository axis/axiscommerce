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

class Axis_Sales_Upgrade_0_2_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.0';
    protected $_info = 'Locale column added to order row';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order')}`
            ADD COLUMN `locale` CHAR(5) NOT NULL DEFAULT 'en_US' AFTER `customer_id`;

        ");

        Axis_Core_Model_Cache::getCache()->clean();
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('sales_order')}`
             DROP COLUMN `locale`;

        ");
    }
}