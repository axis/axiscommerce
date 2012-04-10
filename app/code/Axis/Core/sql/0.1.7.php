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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Core_Upgrade_0_1_7 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.7';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_cache')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_cache')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `name` varchar(64) NOT NULL,
            `is_active` tinyint(3) unsigned NOT NULL DEFAULT '1',
            `lifetime` int(10) unsigned DEFAULT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_config_field')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_config_field')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `lvl` tinyint(3) unsigned NOT NULL,
            `path` varchar(255) NOT NULL,
            `title` varchar(128) NOT NULL,
            `type` varchar(128) NOT NULL DEFAULT '',
            `model` varchar(128) NOT NULL,
            `description` text,
            `translation_module` VARCHAR(45) DEFAULT NULL,
            PRIMARY KEY  (`id`),
            KEY `index_path` USING BTREE (`path`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_config_value')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_config_value')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `config_field_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
            `path` varchar(128) NOT NULL,
            `site_id` smallint(5) unsigned NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `config_value_site_id` (`site_id`),
            KEY `FK_config_field_id` (`config_field_id`),
            CONSTRAINT `FK_config_field_id` FOREIGN KEY (`config_field_id`) REFERENCES `{$installer->getTable('core_config_field')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_module')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_module')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `package` varchar(64) NOT NULL,
            `code` varchar(64) NOT NULL,
            `name` varchar(64) NOT NULL,
            `version` varchar(10) NOT NULL,
            `is_active` tinyint(1) unsigned NOT NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        CREATE TABLE  `{$installer->getTable('core_module_upgrade')}` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `module_id` int(10) unsigned NOT NULL,
          `version` varchar(100) NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `fk_module_id` (`module_id`),
          CONSTRAINT `fk_module_id` FOREIGN KEY (`module_id`) REFERENCES `{$installer->getTable('core_module')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_page')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_page')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `module_name` varchar(64) NOT NULL,
            `controller_name` varchar(64) NOT NULL,
            `action_name` varchar(20) NOT NULL DEFAULT '',
            PRIMARY KEY  USING BTREE (`id`),
            KEY `i_page_1` (`module_name`,`controller_name`,`action_name`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;


        -- DROP TABLE IF EXISTS `{$installer->getTable('core_site')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_site')}` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `base` varchar(100) NOT NULL,
          `name` varchar(100) NOT NULL,
          `secure` varchar(100) NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `i_base_url` USING BTREE (`base`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_template')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_template')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `name` varchar(128) NOT NULL,
            `is_active` tinyint(1) NOT NULL,
            `default_layout` varchar(32) NOT NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_template_box')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_template_box')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `template_id` mediumint(8) unsigned NOT NULL,
            `block` varchar(64) NOT NULL DEFAULT 'content',
            `class` varchar(64) NOT NULL,
            `sort_order` tinyint(3) NOT NULL DEFAULT '100',
            `config` text NOT NULL DEFAULT '',
            `box_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
            PRIMARY KEY  (`id`),
            KEY `template_boxes_FK_template` (`template_id`),
            KEY `i_box_order` (`sort_order`),
            CONSTRAINT `FK_core_template_box_template_id` FOREIGN KEY (`template_id`) REFERENCES `{$installer->getTable('core_template')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_template_box_page')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_template_box_page')}` (
            `box_id` mediumint(8) unsigned NOT NULL,
            `page_id` mediumint(8) unsigned NOT NULL,
            `box_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
            `template` varchar(64) default NULL,
            `block` varchar(32) default NULL,
            `tab_container` VARCHAR(64) DEFAULT NULL,
            `sort_order` tinyint(3) NOT NULL DEFAULT '100',
            PRIMARY KEY  (`box_id`,`page_id`),
            KEY `FK_core_template_box_page_page_id` (`page_id`),
            CONSTRAINT `FK_core_template_box_page_box_id` FOREIGN KEY (`box_id`) REFERENCES `{$installer->getTable('core_template_box')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_core_template_box_page_page_id` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('core_page')}` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_template_layout_page')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_template_layout_page')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `template_id` mediumint(8) unsigned NOT NULL,
            `page_id` mediumint(8) unsigned NOT NULL,
            `layout` varchar(64) NOT NULL,
            `priority` smallint(5) unsigned NOT NULL DEFAULT '100',
            PRIMARY KEY  (`id`),
            KEY `FK_template_layout_to_page_template_id` (`template_id`),
            KEY `FK_template_layout_to_page_page_id` (`page_id`),
            CONSTRAINT `FK_template_layout_to_page_template_id` FOREIGN KEY (`template_id`) REFERENCES `{$installer->getTable('core_template')}` (`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_template_layout_to_page_page_id` FOREIGN KEY (`page_id`) REFERENCES `{$installer->getTable('core_page')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('core_template_mail')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('core_template_mail')}` (
            `id` int(11) NOT NULL auto_increment,
            `name` varchar(64) NOT NULL,
            `template` varchar(128) NOT NULL,
            `event` varchar(64) NOT NULL,
            `status` tinyint(1) NOT NULL,
            `from` varchar(96) NOT NULL,
            `site` varchar(128) NOT NULL,
            `type` varchar(4) NOT NULL DEFAULT 'html',
            PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9;

        INSERT INTO `{$installer->getTable('core_template_mail')}` (`id`, `name`, `template`, `event`, `status`, `from`, `site`, `type`) VALUES
            (1, 'Store', 'default', 'default', 1, 'email2', '1', 'html'),
            (2, 'Contact Us', 'contact-us', 'contact_us', 1, 'email2', '1', 'html'),
            (3, 'Forgot Password', 'forgot', 'forgot_password', 1, 'email2', '1', 'html'),
            (4, 'Notice customer', 'account_new-customer', 'account_new-customer', 1, 'email2', '1', 'html'),
            (5, 'Notice owner', 'account_new-owner', 'account_new-owner', 1, 'email2', '1', 'html'),
            (6, 'Notice customer', 'order_new-customer', 'order_new-customer', 1, 'email2', '1', 'html'),
            (7, 'Notice owner', 'order_new-owner', 'order_new-owner', 1, 'email2', '1', 'html'),
            (8, 'Cusromer notify change order status', 'change_order_status-customer', 'change_order_status-customer', 1, 'email2', '1', 'html');

        /* Admin tables */

        -- DROP TABLE IF EXISTS `{$installer->getTable('admin_acl_role')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('admin_acl_role')}` (
          `id` mediumint(8) unsigned NOT NULL auto_increment,
          `sort_order` tinyint(3) unsigned NOT NULL default '0',
          `role_name` varchar(128) NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('admin_acl_role')}` (`id`, `sort_order`, `role_name`) VALUES
            (1, 0, 'Administrator');

        -- DROP TABLE IF EXISTS `{$installer->getTable('admin_acl_rule')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('admin_acl_rule')}` (
          `role_id` mediumint(8) unsigned NOT NULL,
          `resource_id` varchar(128) NOT NULL,
          `permission` enum('allow','deny') NOT NULL,
          PRIMARY KEY  (`role_id`,`resource_id`),
          KEY `resource` (`resource_id`),
          KEY `i_acl_rule_id` (`role_id`),
          CONSTRAINT `fk_acl_role_id` FOREIGN KEY (`role_id`) REFERENCES `{$installer->getTable('admin_acl_role')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        INSERT INTO `{$installer->getTable('admin_acl_rule')}` (`role_id`, `resource_id`, `permission`) VALUES
            (1, 'admin', 'allow');

        -- DROP TABLE IF EXISTS `{$installer->getTable('admin_menu')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('admin_menu')}` (
          `id` int(11) NOT NULL auto_increment,
          `parent_id` int(11) default NULL,
          `title` varchar(45) NOT NULL,
          `link` varchar(256) DEFAULT NULL,
          `lvl` tinyint(1) NOT NULL,
          `sort_order` tinyint(3) NOT NULL,
          `has_children` tinyint(1) NOT NULL,
          `translation_module` VARCHAR(45) DEFAULT NULL,
          PRIMARY KEY  (`id`),
          KEY `FK_admin_menu_parent_id` (`parent_id`),
          CONSTRAINT `FK_admin_menu_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `{$installer->getTable('admin_menu')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        -- DROP TABLE IF EXISTS `{$installer->getTable('admin_user')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('admin_user')}` (
          `id` mediumint(9) unsigned NOT NULL auto_increment,
          `role_id` mediumint(8) unsigned default NULL,
          `firstname` varchar(32) NOT NULL,
          `lastname` varchar(32) NOT NULL,
          `email` varchar(128) NOT NULL,
          `username` varchar(40) NOT NULL,
          `password` varchar(32) NOT NULL,
          `created` datetime NOT NULL default '0000-00-00 00:00:00',
          `modified` datetime NOT NULL default '0000-00-00 00:00:00',
          `lastlogin` datetime NOT NULL default '0000-00-00 00:00:00',
          `lognum` smallint(5) unsigned NOT NULL default '0',
          `reload_acl_flag` tinyint(1) unsigned NOT NULL default '0',
          `is_active` tinyint(1) unsigned NOT NULL default '1',
          PRIMARY KEY  (`id`),
          KEY `i_admin_user_role_id` (`role_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('admin_user')}` (`id`, `role_id`, `firstname`, `lastname`, `email`, `username`, `password`, `created`, `modified`, `lastlogin`, `lognum`, `reload_acl_flag`, `is_active`) VALUES
            (1, 1, 'admin', 'admin', 'axiscommerce@example.com', 'admin', '733d7be2196ff70efaf6913fc8bdcabf', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 1);

        -- DROP TABLE IF EXISTS `{$installer->getTable('admin_user_forgotpassword')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('admin_user_forgotpassword')}` (
          `user_id` mediumint(9) unsigned NOT NULL,
          `hash` char(32) NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY  (`user_id`),
          CONSTRAINT `FK_admin_user_forgot_password` FOREIGN KEY (`user_id`) REFERENCES `{$installer->getTable('admin_user')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ");

        Axis::single('core/config_field')
            ->add('core', 'Core', null, null, array('translation_module' => 'Axis_Core'))

            ->add('core/store/name', 'Core/Store/Name', 'Enter store name')
            ->add('core/store/city', 'City', '')
            ->add('core/store/country', 'Country', 223, 'select', 'Store Country', array('model' => 'location/option_country'))
            ->add('core/store/zone', 'Zone', 43, 'select', 'Store zone(state,province)', array('model' => 'core/option_store_zone'))
            ->add('core/store/zip', 'Zip code', '10001', 'text', 'Zip code')
            ->add('core/store/owner', 'Store owner', 'Owner')

            ->add('core/backend/route', 'Core/Backend/Route', 'admin', 'text', 'Admin url (example.com/<b>adminRoute</b>)')
            ->add('core/backend/ssl', 'Ssl Enabled', 0, 'radio', '', array('model'=> 'core/option_boolean'))

            ->add('core/frontend/ssl', 'Core/Frontend/Ssl Enabled', 0, 'radio', '', array('model'=> 'core/option_boolean'))

            ->add('core/company/name', 'Core/Company/Name', 'Axiscommerce', 'text', 'Company name')
            ->add('core/company/site', 'Website', 'www.example.com', 'text', 'Company website')
            ->add('core/company/country', 'Country',  223, 'select', 'Company country', array('model' => 'location/option_country'))
            ->add('core/company/city', 'City', 'New York')
            ->add('core/company/zone', 'Zone', 43, 'select', array('model' => 'core/option_company_zone'))
            ->add('core/company/street', 'Street', 'Enter this your street')
            ->add('core/company/zip', 'Zip code', '10001')
            ->add('core/company/phone', 'Phone', '')
            ->add('core/company/fax', 'Fax', '')
            ->add('core/company/administratorEmail', 'Administrator email',  'email1', 'select', array('model' => 'core/option_mail_boxes'))
            ->add('core/company/customerRelationEmail', 'Customer relations email',  'email3', 'select', array('model' => 'core/option_mail_boxes'))
            ->add('core/company/salesDepartmentEmail', 'Sales department email',  'email4', 'select', array('model' => 'core/option_mail_boxes'))
            ->add('core/company/supportEmail', 'Support email', 'email5', 'select', array('model' => 'core/option_mail_boxes'))

            ->add('core/cache/default_lifetime', 'core/Cache/Default Lifetime', '86400')
            ->add('core/translation/autodetect', 'core/Translation/Autodetect new words',  '0', 'radio', 'Detect not translated words and write them to the file (Make sure that locale folder has writable permissions: >chmod -R 0777 [root_path]/app/locale)', array('model'=> 'core/option_boolean'))

            ->add('core/minify/js_front', 'Core/Minify/Enable javascript merging the frontend', '0', 'radio', '', array('model'=> 'core/option_boolean'))
            ->add('core/minify/js_admin', 'Core/Minify/Enable javascript merging on the backend', '0', 'radio', '', array('model'=> 'core/option_boolean'))
            ->add('core/minify/css_front', 'Enable css merging on the frontend', '0', 'radio', '', array('model'=> 'core/option_boolean'))
            ->add('core/minify/css_admin', 'Enable css merging on the backend', '0', 'radio', '', array('model'=> 'core/option_boolean'))

            ->add('mail', 'Mail', null, null, array('translation_module' => 'Axis_Core'))
            ->add('mail/main/mtcFrom', 'Mail/General/Sender', 'email2', 'select', array('model' => 'core/option_mail_boxes'))
            ->add('mail/main/transport', 'Mail transport', Axis_Core_Model_Option_Mail_Transport::SENDMAIL, 'select', 'Mail Transport (smtp or sendmail)', array('model' => 'core/option_mail_transport'))
            ->add('mail/smtp/host', 'Mail/Smtp/Host', 'host.smtp.com')
            ->add('mail/smtp/user', 'User', 'test+axiscommerce.com', 'text', '', array('model' => 'core/option_crypt'))
            ->add('mail/smtp/password', 'Password', 'test', 'text', '', array('model' => 'core/option_crypt'))
            ->add('mail/smtp/port', 'Port', '465')
            ->add('mail/smtp/auth', 'Use Auth', '1', 'radio', '', array('model'=> 'core/option_boolean'))
            ->add('mail/smtp/secure', 'Secure', Axis_Core_Model_Option_Mail_Secure::SSL, 'select', array('model' => 'core/option_mail_secure'))
            ->add('mail/mailboxes/email1', 'Mail/Mailboxes/Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email2', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email3', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email4', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email5', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email6', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email7', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email8', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email9', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email10', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email11', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email12', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email13', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email14', 'Email', 'test@axiscommerce.com')
            ->add('mail/mailboxes/email15', 'Email', 'test@axiscommerce.com')

            ->add('design', 'Design', null, null, array('translation_module' => 'Axis_Core'))
            ->add('design/main/frontTemplateId', 'Design/General/Front Template', 2, 'select', array('model' => 'core/option_template'))
            ->add('design/main/adminTemplateId', 'Admin Template',  1, 'select', array('model' => 'core/option_template'))
            ->add('design/htmlHead/defaultTitle', 'Design/HTML Head/Default Title', 'Default Title')
            ->add('design/htmlHead/defaultDescription', 'Default Description', 'Default Description',  'textarea')
            ->add('design/htmlHead/defaultKeywords', 'Default Keywords',  'Axis, store', 'textarea')
            ->add('design/htmlHead/titlePrefix', 'Title Prefix')
            ->add('design/htmlHead/titleSuffix', 'Title Suffix')
            ->add('design/htmlHead/titleDivider', 'Title Divider', ' - ')
            ->add('design/htmlHead/titlePattern', 'Title Pattern', Axis_Core_Model_Option_Template_TitlePattern::getConfigOptionDafultValue(), 'multiple', 'Check values, which you want to see on page title', array('model' => 'core/option_template_titlePattern'))
            ->add('design/htmlHead/defaultRobots', 'Default Robots', Axis_Core_Model_Option_Template_Robots::getDeafult(), 'select', array('model' => 'core/option_template_robots'))
            ->add('design/htmlHead/homeDescription', 'Homepage description', '', 'textarea', 'Homepage description')
            ->add('design/htmlHead/homeKeywords', 'Homepage keywords', 'Axis, store', 'textarea')
            ->add('design/htmlHead/homeTitle', 'Homepage title', 'Homepage title', 'text', 'Homepage title')
            ;

        Axis::single('core/cache')
            ->add('modules', 1, 864000) //10 days
            ->add('config', 0, 864000)
            ->add('query', 0)
            ->add('Zend_Translate', 1, 864000);

        Axis::single('core/page')
            ->add('*/*/*')
            ->add('core/index/index')
            ->add('core/error/*')
            ->add('core/error/not-found');
    }
}