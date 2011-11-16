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
 * @package     Axis_Csv
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Csv_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('csv_profile')}`;
        CREATE TABLE  `{$installer->getTable('csv_profile')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(128) NOT NULL,
          `type` varchar(32) NOT NULL,
          `direction` enum('export','import') NOT NULL,
          `site` varchar(20) NOT NULL,
          `file_path` varchar(255) NOT NULL,
          `file_name` varchar(90) NOT NULL,
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('csv_profile_filter')}`;
        CREATE TABLE  `{$installer->getTable('csv_profile_filter')}` (
          `profile_id` int(10) unsigned NOT NULL,
          `language_ids` varchar(128) NOT NULL,
          `name` varchar(128) NOT NULL,
          `sku` varchar(128) NOT NULL,
          `stock` tinyint(3) unsigned NOT NULL default '2',
          `status` tinyint(3) unsigned NOT NULL default '2',
          `price_from` int(10) unsigned default NULL,
          `price_to` int(10) unsigned default NULL,
          `qty_from` int(10) unsigned default NULL,
          `qty_to` int(10) unsigned default NULL,
          PRIMARY KEY  (`profile_id`),
          CONSTRAINT `FK_csv_profile_filter_id` FOREIGN KEY (`profile_id`) REFERENCES `{$installer->getTable('csv_profile')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ");
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('csv_profile')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('csv_profile_filter')}`;
        ");
    }
}