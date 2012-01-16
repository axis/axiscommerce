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

class Axis_Sales_Upgrade_0_2_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.2';
    protected $_info = 'ACL rule(s) was modified';

    public function up()
    {
        $rowset = Axis::model('admin/acl_rule')->select()
            ->where("resource_id LIKE 'admin/sales%'")
            ->fetchRowset();
        
        foreach ($rowset as $row) {
            $row->resource_id = str_replace(
                'admin/sales', 'admin/axis/sales', $row->resource_id
            );
            $row->save();
        }
    }
}