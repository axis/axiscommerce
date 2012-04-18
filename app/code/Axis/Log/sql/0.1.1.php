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

class Axis_Log_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('log_url')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('log_url')}` (
          `url_id` int(11) NOT NULL,
          `visitor_id` int(11) NOT NULL,
          `visit_at` datetime default NULL,
          `site_id` smallint(9) NOT NULL,
          PRIMARY KEY  (`url_id`,`visitor_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('log_url_info')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('log_url_info')}` (
          `id` mediumint(7) NOT NULL auto_increment,
          `url` varchar(255) default NULL,
          `refer` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('log_visitor')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('log_visitor')}` (
          `id` mediumint(7) unsigned NOT NULL auto_increment,
          `session_id` char(32) default NULL,
          `customer_id` int(11) default NULL,
          `last_url_id` int(11) default NULL,
          `last_visit_at` datetime default NULL,
          `site_id` smallint(9) NOT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `UNQ_LOG_VISITOR` (`session_id`,`customer_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('log_visitor_info')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('log_visitor_info')}` (
          `visitor_id` int(11) NOT NULL,
          `http_refer` varchar(255) default NULL,
          `user_agent` varchar(255) default NULL,
          `http_accept_charset` varchar(128) default NULL,
          `http_accept_language` varchar(128) default NULL,
          `server_addr` varchar(128) default NULL,
          `remote_addr` varchar(128) default NULL,
          PRIMARY KEY  (`visitor_id`),
          KEY `fk_log_visitor_info_log_visitor` (`visitor_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

        ");

        Axis::single('core/config_builder')
            ->section('log', 'Log')
                ->setTranslation('Axis_Log')
                ->section('main', 'General')
                    ->option('enabled', 'Enabled', 1)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('php', 'Php log', '/var/logs/php.log')
                        ->setDescription('Path relative to AXIS_ROOT')
                    ->option('payment', 'Payment log', '/var/logs/payment.log')
                        ->setDescription('Path relative to AXIS_ROOT')
                    ->option('shipping', 'Shipping log', '/var/logs/shipping.log')
                        ->setDescription('Path relative to AXIS_ROOT')

            ->section('/');

        Axis::single('core/page')
            ->add('account/*/*');
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('log_url')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('log_url_info')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('log_visitor')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('log_visitor_info')}`;
        ");

        Axis::single('core/config_field')->remove('log/main/enabled');
        Axis::single('core/config_value')->remove('log/main/enabled');

        //Axis::single('core/template_box')
        //    ->remove('Axis_Log_Visitor')
        //    ->remove('Axis_Log_Customer');
    }
}