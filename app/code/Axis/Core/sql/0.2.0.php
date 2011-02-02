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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_2_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.0';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_resource')
            ->add("admin/template_page")
            ->add("admin/template_page/delete")
            ->add("admin/template_page/list")
            ->add("admin/template_page/save");

        $select = Axis::single('admin/acl_rule')->select()
            ->where("resource_id LIKE ?", 'admin/template_layout%');
        $rowset = Axis::single('admin/acl_rule')->fetchAll($select);
        foreach ($rowset as $row) {
            $resourceId = str_replace(
                'admin/template_layout', 'admin/template_page', $row->resource_id
            );
            $row->resource_id = $resourceId;
            $row->save();
        }

    }

    public function down()
    {
        
    }
}