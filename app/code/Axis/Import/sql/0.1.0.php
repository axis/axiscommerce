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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Import_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('import_profile')}`;
        CREATE TABLE  IF NOT EXISTS `{$installer->getTable('import_profile')}` (
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
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('import_profile')}`;
        ");
    }
}