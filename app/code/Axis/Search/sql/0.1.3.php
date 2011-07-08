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
 * @package     Axis_Search
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Search_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = 'upgrade';

    public function up()
    {
        Axis::single('admin/acl_resource')->rename(
            'admin/catalog_index/update-search-index', 
            'admin/search/update-index'
        );
        
        $rowset = Axis::single('admin/acl_rule')->select()
            ->where('resource_id = ?', 'admin/catalog_index/update-search-index')
            ->fetchRowset();
        
        foreach ($rowset as $row) {
            $row->resource_id = 'admin/search/update-index';
            $row->save();
        }
    }

    public function down()
    {

    }
}