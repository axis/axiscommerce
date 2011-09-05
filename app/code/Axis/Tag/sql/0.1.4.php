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
 * @package     Axis_Tag
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Tag_Upgrade_0_1_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.4';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/menu')
            ->edit('Tags', null, 'tag/index')
        ;

        Axis::single('admin/acl_resource')
            ->rename('admin/tag_index/index', 'admin/tag/index')
            ->rename('admin/tag_index/list', 'admin/tag/list')
            ->rename('admin/tag_index/save', 'admin/tag/batch-save')
            ->rename('admin/tag_index/delete', 'admin/tag/remove')
            ->remove('admin/tag_index')
        ;

    }

    public function down()
    {
    }
}