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

class Axis_Discount_Upgrade_0_0_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.0.1';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('discount')}`;
        CREATE TABLE  `{$installer->getTable('discount')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(255) NOT NULL,
          `description` text,
          `from_date` date default NULL,
          `to_date` date default NULL,
          `is_active` tinyint(1) unsigned NOT NULL default '0',
          `type` varchar(10) NOT NULL,
          `amount` decimal(12,4) NOT NULL,
          `priority` tinyint(2) unsigned default NULL,
          `is_combined` tinyint(1) NOT NULL default '0',
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('discount_eav')}`;
        CREATE TABLE  `{$installer->getTable('discount_eav')}` (
          `discount_id` int(10) unsigned NOT NULL,
          `entity` varchar(25) NOT NULL,
          `value` int(11) NOT NULL,
          KEY `discount_fk_constraint` (`discount_id`),
          CONSTRAINT `discount_fk_constraint` FOREIGN KEY (`discount_id`) REFERENCES `{$installer->getTable('discount')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        ");
    }

    public function down()
    {

    }
}