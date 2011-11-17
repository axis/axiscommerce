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
 * @package     Axis_Contacts
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Contacts_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_rule')
            ->rename('admin/contacts_index',            'admin/contacts/index')
            ->rename('admin/contacts_index/index',      'admin/contacts/index/index')
            ->rename('admin/contacts_index/list',       'admin/contacts/index/list')
            ->rename('admin/contacts_index/set-status', 'admin/contacts/index/save')
            ->rename('admin/contacts_index/delete',     'admin/contacts/index/remove')
            ->rename('admin/contacts_index/send',       'admin/contacts/index/send')
            
            ->rename('admin/contacts_index/get-departments',   'admin/contacts/department/list')
            ->rename('admin/contacts_index/get-department',    'admin/contacts/department/load')
            ->rename('admin/contacts_index/save-department',   'admin/contacts/department/save')
            ->rename('admin/contacts_index/delete-department', 'admin/contacts/department/remove')
        ;

    }
}