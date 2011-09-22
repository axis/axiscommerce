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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Account_Upgrade_0_1_6 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.6';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer')}` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `email` varchar(100) NOT NULL,
                `password` char(32) NOT NULL,
                `firstname` varchar(64) NOT NULL,
                `lastname` varchar(64) NOT NULL,
                `site_id` smallint(5) unsigned default NULL,
                `is_active` tinyint(1) unsigned NOT NULL default '0',
                `default_shipping_address_id` int(10) unsigned default NULL,
                `default_billing_address_id` int(10) unsigned default NULL,
                `created_at` date NOT NULL,
                `modified_at` date NOT NULL,
                `group_id` smallint(5) unsigned default '1',
                PRIMARY KEY  (`id`),
                KEY `FK_account_customer_site` (`site_id`),
                KEY `FK_account_customer_group` (`group_id`),
                CONSTRAINT `FK_account_customer_site` FOREIGN KEY (`site_id`) REFERENCES `{$installer->getTable('core_site')}` (`id`) ON DELETE SET NULL,
                CONSTRAINT `FK_account_customer_group` FOREIGN KEY (`group_id`) REFERENCES `{$installer->getTable('account_customer_group')}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_address')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_address')}` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `customer_id` int(10) UNSIGNED NOT NULL,
              `gender` char(1) default '',
              `company` varchar(128) default NULL,
              `phone` varchar(64) NOT NULL,
              `fax` varchar(64) NOT NULL,
              `firstname` varchar(128) NOT NULL,
              `lastname` varchar(128) NOT NULL,
              `street_address` varchar(128) NOT NULL,
              `suburb` varchar(128) default NULL,
              `postcode` varchar(20) NOT NULL,
              `city` varchar(64) NOT NULL,
              `country_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
              `zone_id` MEDIUMINT(8) UNSIGNED DEFAULT NULL,
              PRIMARY KEY  (`id`),
              KEY `FK_ACCOUNT_CUSTOMER_ADDRESS_CUSTOMER` (`customer_id`),
              KEY `FK_ACCOUNT_CUSTOMER_ADDRESS_COUNTRY` (`country_id`),
              CONSTRAINT `FK_ACCOUNT_CUSTOMER_ADDRESS_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `FK_ACCOUNT_CUSTOMER_ADDRESS_COUNTRY` FOREIGN KEY (`country_id`) REFERENCES `{$installer->getTable('location_country')}` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `FK_ACCOUNT_CUSTOMER_ADDRESS_ZONE` FOREIGN KEY (`zone_id`) REFERENCES `{$installer->getTable('location_zone')}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_detail')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_detail')}` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `customer_id` int(10) unsigned NOT NULL,
              `customer_field_id` mediumint(8) unsigned NOT NULL,
              `customer_valueset_value_id` mediumint(8) unsigned default NULL,
              `data` text,
              PRIMARY KEY  (`id`),
              KEY `FK_account_customer_detail_customer` (`customer_id`),
              KEY `FK_account_customer_detail_field` (`customer_field_id`),
              KEY `FK_account_customer_detail_valueset_value` (`customer_valueset_value_id`),
              CONSTRAINT `FK_account_customer_detail_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_detail_field` FOREIGN KEY (`customer_field_id`) REFERENCES `{$installer->getTable('account_customer_field')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_detail_valueset_value` FOREIGN KEY (`customer_valueset_value_id`) REFERENCES `{$installer->getTable('account_customer_valueset_value')}` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_field')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_field')}` (
              `id` mediumint(8) unsigned NOT NULL auto_increment,
              `name` varchar(128) NOT NULL,
              `customer_field_group_id` smallint(5) unsigned NOT NULL,
              `field_type` varchar(128) NOT NULL,
              `required` tinyint(1) unsigned NOT NULL default '0',
              `sort_order` tinyint(3) unsigned NOT NULL default '5',
              `is_active` tinyint(1) unsigned NOT NULL default '1',
              `customer_valueset_id` smallint(5) unsigned default NULL,
              `validator` varchar(128) default NULL,
              `axis_validator` VARCHAR(128) default NULL,
              PRIMARY KEY  (`id`),
              KEY `FK_account_customer_field_group` (`customer_field_group_id`),
              KEY `FK_account_customer_field_valueset` (`customer_valueset_id`),
              CONSTRAINT `FK_account_customer_field_group` FOREIGN KEY (`customer_field_group_id`) REFERENCES `{$installer->getTable('account_customer_fieldgroup')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_field_valueset` FOREIGN KEY (`customer_valueset_id`) REFERENCES `{$installer->getTable('account_customer_valueset')}` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=3;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_fieldgroup')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_fieldgroup')}` (
              `id` smallint(5) unsigned NOT NULL auto_increment,
              `name` VARCHAR(128) NOT NULL,
              `sort_order` smallint(5) unsigned NOT NULL default '0',
              `is_active` tinyint(1) unsigned NOT NULL,
              PRIMARY KEY  (`id`),
              UNIQUE KEY `unique_name` (`name`),
              KEY `Index_sort_order` (`sort_order`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_fieldgroup_label')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_fieldgroup_label')}` (
              `customer_field_group_id` smallint(5) unsigned NOT NULL,
              `language_id` smallint(5) unsigned NOT NULL,
              `group_label` varchar(45) NOT NULL,
              PRIMARY KEY  USING BTREE (`customer_field_group_id`,`language_id`),
              KEY `FK_account_customer_field_group_label_language` (`language_id`),
              CONSTRAINT `FK_account_customer_field_group_label_fieldgroup` FOREIGN KEY (`customer_field_group_id`) REFERENCES `{$installer->getTable('account_customer_fieldgroup')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_field_group_label_language` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_field_label')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_field_label')}` (
              `customer_field_id` mediumint(8) unsigned NOT NULL,
              `language_id` smallint(5) unsigned NOT NULL,
              `field_label` varchar(128) NOT NULL,
              PRIMARY KEY  USING BTREE (`customer_field_id`,`language_id`),
              KEY `FK_account_customer_field_label_language` (`language_id`),
              CONSTRAINT `FK_account_customer_field_label_customer_field` FOREIGN KEY (`customer_field_id`) REFERENCES `{$installer->getTable('account_customer_field')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_field_label_language` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_forgotpassword')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_forgotpassword')}` (
              `customer_id` int(10) unsigned NOT NULL,
              `hash` char(32) NOT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY  (`customer_id`),
              CONSTRAINT `FK_account_customer_forgot_password_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_group')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_group')}` (
              `id` smallint(5) unsigned NOT NULL auto_increment,
              `name` varchar(128) NOT NULL,
              `description` varchar(128) NOT NULL,
              PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

            INSERT INTO `{$installer->getTable('account_customer_group')}` (`id`, `name`, `description`) VALUES
              (1, 'General', ''),(2, 'Retailer', ''),(3, 'Wholesale', ''),
              (4, 'Banned', ''), (" . Axis_Account_Model_Customer_Group::GROUP_GUEST_ID . ", 'Guest', '');

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_valueset')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_valueset')}` (
              `id` smallint(5) unsigned NOT NULL auto_increment,
              `name` varchar(128) NOT NULL,
              PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_valueset_value')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_valueset_value')}` (
              `id` mediumint(8) unsigned NOT NULL auto_increment,
              `customer_valueset_id` smallint(5) unsigned NOT NULL,
              `sort_order` smallint(5) unsigned NOT NULL,
              `is_active` tinyint(1) unsigned NOT NULL,
              PRIMARY KEY  USING BTREE (`id`),
              KEY `FK_account_customer_valueset_value_valueset` (`customer_valueset_id`),
              CONSTRAINT `FK_account_customer_valueset_value_valueset` FOREIGN KEY (`customer_valueset_id`) REFERENCES `{$installer->getTable('account_customer_valueset')}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_customer_valueset_value_label')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_customer_valueset_value_label')}` (
              `valueset_value_id` mediumint(8) unsigned NOT NULL,
              `language_id` smallint(5) unsigned NOT NULL,
              `label` varchar(128) NOT NULL,
              PRIMARY KEY  (`valueset_value_id`,`language_id`),
              KEY `FK_account_customer_valueset_value_label_language` (`language_id`),
              CONSTRAINT `FK_account_customer_valueset_value_label_valueset_value` FOREIGN KEY (`valueset_value_id`) REFERENCES `{$installer->getTable('account_customer_valueset_value')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_valueset_value_label_language` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

            -- DROP TABLE IF EXISTS `{$installer->getTable('account_wishlist')}`;
            CREATE TABLE IF NOT EXISTS `{$installer->getTable('account_wishlist')}` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `customer_id` int(10) unsigned NOT NULL,
              `product_id` int(10) unsigned NOT NULL,
              `created_on` datetime NOT NULL,
              `wish_comment` text,
              PRIMARY KEY  (`id`),
              UNIQUE KEY `UNIQUE_wishlist_set` (`customer_id`,`product_id`),
              KEY `customer_wishlist_FKIndex_customer` (`customer_id`),
              KEY `customer_wishlist_FKIndex_product` (`product_id`),
              CONSTRAINT `FK_account_customer_wishlist_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('account_customer')}` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_account_customer_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product')}` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;

        ");

        Axis::single('admin/acl_resource')
            ->add('admin/customer', 'Customers')
            ->add('admin/customer_index', 'Manage Customers')
            ->add("admin/customer_index/create")
            ->add("admin/customer_index/delete")
            ->add("admin/customer_index/edit")
            ->add("admin/customer_index/edit-details")
            ->add("admin/customer_index/index")
            ->add("admin/customer_index/list")
            ->add("admin/customer_index/load-fields-data")
            ->add("admin/customer_index/quick-save")
            ->add("admin/customer_index/save")
            ->add("admin/customer_index/save-all")

            ->add('admin/customer_group', 'Customer Groups')
            ->add("admin/customer_group/delete")
            ->add("admin/customer_group/get-groups")
            ->add("admin/customer_group/index")
            ->add("admin/customer_group/list")
            ->add("admin/customer_group/save")

            ->add('admin/customer_wishlist', 'Customer Wishlist')
            ->add("admin/customer_wishlist/index")
            ->add("admin/customer_wishlist/list")

            ->add('admin/customer_custom-fields', 'Customer Info Fields')
            ->add("admin/customer_custom-fields/ajax-delete-group")
            ->add("admin/customer_custom-fields/ajax-delete-value-set")
            ->add("admin/customer_custom-fields/ajax-delete-value-set-values")
            ->add("admin/customer_custom-fields/ajax-save-group")
            ->add("admin/customer_custom-fields/ajax-save-value-set")
            ->add("admin/customer_custom-fields/ajax-save-value-set-values")
            ->add("admin/customer_custom-fields/batch-save-fields")
            ->add("admin/customer_custom-fields/delete-fields")
            ->add("admin/customer_custom-fields/get-fields")
            ->add("admin/customer_custom-fields/get-group-info")
            ->add("admin/customer_custom-fields/get-groups")
            ->add("admin/customer_custom-fields/get-type")
            ->add("admin/customer_custom-fields/get-validator")
            ->add("admin/customer_custom-fields/get-value-sets")
            ->add("admin/customer_custom-fields/get-values")
            ->add("admin/customer_custom-fields/index")
            ->add("admin/customer_custom-fields/save-field")

            ->add("admin/customer_email")
            ->add("admin/customer_email/send");

        Axis::single('core/page')
            ->add('account/*/*')
            ->add('account/auth/*')
            ->add('account/forgot/*')
            ->add('account/info/*')
            ->add('account/tag/*')
            ->add('account/wishlist/*')
            ->add('account/address-book/*');

        Axis::single('core/config_field')
            ->add('account', 'Account', null, null, array('translation_module' => 'Axis_Account'))
            ->add('account/main/defaultCustomerGroup', 'Account/General/Default Customer Group', 1, 'select', "Default Customer Group (default:'General')", array('model' => 'CustomerGroup'));
    }

    public function down()
    {

    }
}