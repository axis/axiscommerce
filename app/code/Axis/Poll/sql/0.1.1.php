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
 * @package     Axis_Poll
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Poll_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('poll_answer')}`;
        CREATE TABLE  `{$installer->getTable('poll_answer')}` (
          `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
          `language_id` smallint(5) unsigned NOT NULL,
          `question_id` mediumint(8) unsigned NOT NULL,
          `answer` varchar(255) DEFAULT NULL,
          PRIMARY KEY USING BTREE (`id`,`language_id`),
          KEY `FK_POLL_ANSWER_LANGUAGE` (`language_id`),
          KEY `FK_POLL_ANSWER_QUESTION` (`question_id`),
          CONSTRAINT `FK_POLL_ANSWER_LANGUAGE` FOREIGN KEY (`language_id`)
              REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_POLL_ANSWER_QUESTION` FOREIGN KEY (`question_id`)
              REFERENCES `{$installer->getTable('poll_question')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('poll_question')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('poll_question')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `created_at` datetime default NULL,
            `changed_at` datetime default NULL,
            `status` tinyint(3) unsigned default '1',
            `type` tinyint(3) unsigned NOT NULL,
            PRIMARY KEY  USING BTREE (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('poll_question_description')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('poll_question_description')}` (
            `question_id` mediumint(8) unsigned NOT NULL,
            `language_id` smallint(5) unsigned NOT NULL,
            `question` varchar(255) NOT NULL,
            PRIMARY KEY  (`language_id`,`question_id`),
            KEY `FK_POLL_QUESTION_DESCRIPTION_LANGUAGE` (`language_id`),
            KEY `FK_POLL_QUESTION_DESCRIPTION_QUESTION` (`question_id`),
            CONSTRAINT `FK_POLL_QUESTION_DESCRIPTION_LANGUAGE` FOREIGN KEY (`language_id`)
                REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_POLL_QUESTION_DESCRIPTION_QUESTION` FOREIGN KEY (`question_id`)
                REFERENCES `{$installer->getTable('poll_question')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('poll_question_site')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('poll_question_site')}` (
            `question_id` mediumint(8) unsigned NOT NULL auto_increment,
            `site_id` smallint(5) unsigned NOT NULL,
            PRIMARY KEY  USING BTREE (`question_id`,`site_id`),
            KEY `FK_POLL_QUESTION_SITE_SITE` (`site_id`),
            CONSTRAINT `FK_POLL_QUESTION_SITE_QUESTION` FOREIGN KEY (`question_id`)
                REFERENCES `{$installer->getTable('poll_question')}` (`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_POLL_QUESTION_SITE_SITE` FOREIGN KEY (`site_id`)
                REFERENCES `{$installer->getTable('core_site')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('poll_vote')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('poll_vote')}` (
          `id` int(11) NOT NULL auto_increment,
          `ip` int(11) default NULL,
          `answer_id` mediumint(8) unsigned default NULL,
          `customer_id` int(10) unsigned default NULL,
          `visitor_id` int(10) unsigned default NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `FK_POLL_VOTE_ANSWER` (`answer_id`),
          KEY `FK_POLL_VOTE_CUSTOMER` (`customer_id`),
          UNIQUE KEY `UNQ_POLL_VOTE` (`ip`, `answer_id`, `customer_id`),
          CONSTRAINT `FK_POLL_VOTE_CUSTOMER` FOREIGN KEY (`customer_id`)
              REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_POLL_VOTE_ANSWER` FOREIGN KEY (`answer_id`)
              REFERENCES `{$installer->getTable('poll_answer')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        ");

        Axis::single('admin/menu')
            ->add('CMS', null, 90, 'Axis_Cms')
            ->add('CMS->Polls', 'poll_index', 40, 'Axis_Poll');

        Axis::single('admin/acl_resource')
            ->add('admin/poll', 'Polls All')
            ->add('admin/poll_index', 'Polls')
            ->add("admin/poll_index/clear")
            ->add("admin/poll_index/delete")
            ->add("admin/poll_index/get-question")
            ->add("admin/poll_index/index")
            ->add("admin/poll_index/list")
            ->add("admin/poll_index/quick-save")
            ->add("admin/poll_index/save");

        Axis::single('core/page')
            ->add('poll/*/*');
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('poll_answer')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('poll_question')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('poll_question_description')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('poll_question_site')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('poll_vote')}`;
        ");

        Axis::single('admin/menu')->remove('CMS->Polls');

        Axis::single('admin/acl_resource')->remove('admin/poll');

        //Axis::single('core/template_box')->remove('Axis_Poll_Poll');

        Axis::single('core/page')->remove('poll/*/*');
    }
}