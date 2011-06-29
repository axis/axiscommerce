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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Account_Upgrade_0_1_8 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.8';
    protected $_info = 'upgrade';

    public function up()
    {
        Axis::model('account/customer_group')->createRow(array(
            'id' => Axis_Account_Model_Customer_Group::GROUP_ALL_ID,
            'name' => 'All',
            'description' => 'system group no delete'
        ))->save();
    }

    public function down()
    {
    }
}