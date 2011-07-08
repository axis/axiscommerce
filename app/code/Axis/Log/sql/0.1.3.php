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
 * @package     Axis_Log
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Log_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = 'upgrade';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("
            
            CREATE TABLE  `{$installer->getTable('log_event')}` (
               `id` int(11) NOT NULL AUTO_INCREMENT,
               `visitor_id` int(11) NOT NULL,
               `event_name` varchar(64) NOT NULL,
               `object_id` mediumint(9) NOT NULL,
               PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            
            DELETE FROM `{$installer->getTable('log_url')}`;
            
            ALTER TABLE `{$installer->getTable('log_url')}` 
                ADD COLUMN `id` INTEGER  NOT NULL AUTO_INCREMENT AFTER `visitor_id`,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`id`);

        ");
            
        Axis::single('core/template_box')->remove('Axis_Log_Visitor');
    }

    public function down()
    {
    }
}