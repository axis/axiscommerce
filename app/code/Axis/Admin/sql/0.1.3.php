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
         Axis::single('admin/menu')
            ->edit('Roles', null, 'role')
            ->edit('Admin Users', null, 'user')
         ;
        
        Axis::single('admin/acl_resource')
            ->rename('admin/roles', 'admin/role')
            ->rename('admin/roles/index', 'admin/role/index')
            ->rename('admin/roles/get-nodes', 'admin/role/list')
            ->rename('admin/roles/edit', 'admin/role/load')
            ->rename('admin/roles/add', 'admin/role/add')
            ->rename('admin/roles/save', 'admin/role/save')
            ->rename('admin/roles/delete', 'admin/role/remove')
            ->rename('admin/roles/get-parent-allows', 'admin/role/list-parent')
            ->remove('admin/roles')
            
            ->rename('admin/users', 'admin/user')
            ->rename('admin/users/index', 'admin/user/index')
            ->rename('admin/users/get-list', 'admin/user/list')
            ->rename('admin/users/save', 'admin/user/batch-save')
            ->rename('admin/users/delete', 'admin/user/remove')
            ->remove('admin/users')
        ;
    }

    public function down()
    {

    }
}