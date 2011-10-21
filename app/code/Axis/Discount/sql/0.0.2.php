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
 * @package     Axis_Discount
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Discount_Upgrade_0_0_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.0.2';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_rule')
            ->rename('admin/discount_index',        'admin/discount')
            ->rename('admin/discount_index/index',  'admin/discount/index')
            ->rename('admin/discount_index/edit',   'admin/discount/edit')
            ->rename('admin/discount_index/create', 'admin/discount/create')
            ->rename('admin/discount_index/save',   'admin/discount/save')
            ->rename('admin/discount_index/delete', 'admin/discount/remove')
            ;
    }

    public function down()
    {
    }
}