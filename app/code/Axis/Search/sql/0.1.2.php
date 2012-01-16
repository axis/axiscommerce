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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Search_Upgrade_0_1_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.2';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        if (@preg_match('/\pL/u', 'a') != 1) {
            Axis::message()->addNotice("Axis_Search module :PCRE unicode support is turned off.\n");
        }
        if (!function_exists("mb_strstr")) {
            Axis::message()->addNotice("For current work Axis_Search module need http://php.net/manual/en/mbstring.installation.php");
        }

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('search_log')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('search_log')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `visitor_id` int(10) unsigned NOT NULL,
            `query_id` int(10) unsigned NOT NULL,
            `num_results` mediumint(8) unsigned NOT NULL default '0',
            `created_at` datetime NOT NULL,
            `site_id` smallint(9) NOT NULL,
            `customer_id` int(10) unsigned default NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

        -- DROP TABLE IF EXISTS `{$installer->getTable('search_log_query')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('search_log_query')}` (
            `id` int(11) NOT NULL auto_increment,
            `query` varchar(255) NOT NULL,
            `hit` int(11) NOT NULL default '0',
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        ");

        Axis::single('core/page')
            ->add('search/*/*')
            ->add('search/index/*')
            ->add('search/index/index')
            ->add('search/index/result');
    }
}