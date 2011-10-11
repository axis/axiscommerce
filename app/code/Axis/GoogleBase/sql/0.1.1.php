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
 * @package     Axis_GoogleBase
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_GoogleBase_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_resource')
            ->rename('admin/gbase', 'admin/googlebase')
            ->rename('admin/gbase_index/index', 'admin/googlebase/index')
            ->rename('admin/gbase_index/get-gbase-data', 'admin/googlebase/load')
            ->rename('admin/gbase_index/delete', 'admin/googlebase/remove')
            ->rename('admin/gbase_index/export', 'admin/googlebase/export')
            ->rename('admin/gbase_index/export-branch', 'admin/googlebase/export-branch')
            ->rename('admin/gbase_index/revoke-token', 'admin/googlebase/revoke-token')
            ->rename('admin/gbase_index/set-status', 'admin/googlebase/set-status')
            ->rename('admin/gbase_index/update', 'admin/googlebase/update')
            ->remove('admin/gbase_index');
    }

    public function down()
    {

        Axis::single('admin/acl_resource')
            ->remove('admin/googlebase')
        ;
    }
}