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

class Axis_Admin_Upgrade_0_2_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.2';
    protected $_info = '';
    
    //depends axis_core 0.1.6 upgrade

    public function up()
    {
        Axis::model('admin/menu')
            ->edit('Manage Customers', null, 'account/customer')
            ->edit('Customer Groups', null, 'account/group')
            
        ;
        
        Axis::model('admin/acl_resource')
            ->rename('admin/customer_index/index', 'admin/account/customer/index')
            ->rename('admin/customer_index/list', 'admin/account/customer/list')
            ->rename('admin/customer_index/delete', 'admin/account/customer/remove')
            ->rename('admin/customer_index/save-customer', 'admin/account/customer/save')
            ->rename('admin/customer_index/batch-save', 'admin/account/customer/batch-save')
            ->rename('admin/customer_index/get-address-list', 'admin/account/address/list')
            ->remove('admin/customer_index')
            
            ->rename('admin/customer_group/index', 'admin/account/group/index')
            ->rename('admin/customer_group/list', 'admin/account/group/list')
            ->rename('admin/customer_group/save', 'admin/account/group/batch-save')
            ->rename('admin/customer_group/delete', 'admin/account/group/remove')
            
            ->remove('admin/customer_group')
        ;
    }

    public function down()
    {

    }
}