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

class Axis_Admin_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = '';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        DROP TABLE IF EXISTS `{$installer->getTable('admin_acl_resource')}`;
        
        DROP TABLE IF EXISTS `{$installer->getTable('admin_acl_role_parent')}`;
        
        ALTER TABLE `{$installer->getTable('admin_acl_role')}` 
            CHANGE COLUMN `role_name` `name` VARCHAR(128) NOT NULL;

        ");
        Axis::single('admin/acl_rule')
            ->rename('admin/role',             'admin/axis/admin/acl-role')
            ->rename('admin/role/index',       'admin/axis/admin/acl-role/index')
            ->rename('admin/role/list',        'admin/axis/admin/acl-role/list')
            ->rename('admin/role/load',        'admin/axis/admin/acl-role/role')
            ->rename('admin/role/add',         'admin/axis/admin/acl-role/save')
            ->rename('admin/role/save',        'admin/axis/admin/acl-rule/batch-save')
            ->rename('admin/role/remove',      'admin/axis/admin/acl-role/remove')
            ->rename('admin/role/list-parent', 'admin/axis/admin/acl-resource/list')
            
            ->rename('admin/user',             'admin/axis/admin/user')
            ->rename('admin/user/index',       'admin/axis/admin/user/index')
            ->rename('admin/user/list',        'admin/axis/admin/user/list')
            ->rename('admin/user/batch-save',  'admin/axis/admin/user/batch-save')
            ->rename('admin/user/remove',      'admin/axis/admin/user/remove')
        ;
    }

    public function down()
    {

    }
}