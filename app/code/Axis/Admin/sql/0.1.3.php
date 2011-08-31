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
 * @package     Axis_Admin
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Admin_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = '';

    public function up()
    {
        Axis::model('admin/menu')
            ->edit('Manage Customers', null, 'account/customer')
            
        ;
        
        Axis::model('admin/acl_resource')
            ->rename('admin/customer_index/index', 'admin/account/customer/index')
            ->rename('admin/customer_index/list', 'admin/account/customer/list')
            ->rename('admin/customer_index/delete', 'admin/account/customer/remove')
        ;
    }

    public function down()
    {

    }
}