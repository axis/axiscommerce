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
        
        -- DROP TABLE IF EXISTS `{$installer->getTable('admin_acl_role_parent')}`;

        ");
        /*
         
        $rowset = Axis::model('admin/acl_rule')->select()
            ->fetchRowset();
        
        foreach ($rowset as $row) {
            $row->resource_id = str_replace('admin/', 'admin/axis/', $row->resource_id);
            $row->save();
        }
         *
         */
        // role_name column rename name 
        // resource_id column renamt to resource
        // remove  admin/acl_resource table 
        // remove role guest support from role table 
        // remove const rules from rule table
    }

    public function down()
    {

    }
}