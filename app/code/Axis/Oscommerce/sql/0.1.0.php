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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */


class Axis_Oscommerce_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('import_profile')}`;
        CREATE TABLE  `{$installer->getTable('import_profile')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(128) NOT NULL,
          `host` varchar(255) NOT NULL,
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          `db_name` varchar(255) NOT NULL,
          `db_user` varchar(255) NOT NULL,
          `db_password` varchar(255) NOT NULL,
          `table_prefix` varchar(128) NOT NULL,
          `type` varchar(255) NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        ");

        Axis::single('admin/menu')
            ->add('Administrate', null, 110, 'Axis_Admin')
            ->add('Administrate->Import/Export', null, 70)
            ->add('Administrate->Import/Export->OsCommerce', 'import_index', 20);

        Axis::single('admin/acl_resource')
            ->add('admin/import', 'Import')
            ->add('admin/import_index', 'OsCommerce')
            ->add("admin/import_index/connect")
            ->add("admin/import_index/delete")
            ->add("admin/import_index/disconnect")
            ->add("admin/import_index/get-list")
            ->add("admin/import_index/get-supported-types")
            ->add("admin/import_index/import")
            ->add("admin/import_index/index")
            ->add("admin/import_index/save");
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('import_profile')}`;
        ");

        Axis::single('admin/menu')->remove('Administrate->Import/Export->OsCommerce');

        Axis::single('admin/acl_resource')
            ->add('admin/import');
    }
}