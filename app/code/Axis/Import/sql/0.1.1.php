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
 * @package     Axis_Import
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Import_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = '';

    public function up()
    {
        Axis::single('core/module')->delete("code = 'Axis_Oscommerce'");

        Axis::single('admin/acl_rule')
            ->rename('admin/import_index/index',               'admin/import/index')
            ->rename('admin/import_index/get-list',            'admin/import/list')
            ->rename('admin/import_index/save',                'admin/import/save')
            ->rename('admin/import_index/delete',              'admin/import/remove')
            ->rename('admin/import_index/connect',             'admin/import/connect')
            ->rename('admin/import_index/disconnect',          'admin/import/disconnect')
            ->rename('admin/import_index/get-supported-types', 'admin/import/list-type')
            ->rename('admin/import_index/import',              'admin/import/import')
            ;
    }

    public function down()
    {
//        $installer = $this->getInstaller();
//
//        $installer->run('
//            DROP TABLE IF EXISTS `{$installer->getTable('import_profile')}`;
//        ');
    }
}