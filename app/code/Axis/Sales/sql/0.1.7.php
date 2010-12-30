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
 * @package     Axis_Sales
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sales_Upgrade_0_1_7 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.7';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `number` varchar(32) NOT NULL,
            `customer_id` int(10) unsigned default '0',
            `customer_email` varchar(96) default NULL,
            `delivery_firstname` varchar(128) NOT NULL,
            `delivery_lastname` varchar(128) NOT NULL,
            `delivery_phone` VARCHAR(64) NOT NULL,
            `delivery_fax` VARCHAR(64) DEFAULT NULL,
            `delivery_company` varchar(128) default NULL,
            `delivery_street_address` varchar(128) default NULL,
            `delivery_suburb` varchar(128) default NULL,
            `delivery_city` varchar(64) default NULL,
            `delivery_postcode` varchar(20) default NULL,
            `delivery_state` varchar(64) default NULL,
            `delivery_country` varchar(64) default NULL,
            `delivery_address_format_id` int(5) default '0',
            `billing_firstname` varchar(128) NOT NULL,
            `billing_lastname` varchar(128) NOT NULL,
            `billing_phone` VARCHAR(64) NOT NULL,
            `billing_fax` VARCHAR(64) DEFAULT NULL,
            `billing_company` varchar(128) default NULL,
            `billing_street_address` varchar(64) default NULL,
            `billing_suburb` varchar(128) default NULL,
            `billing_city` varchar(64) default NULL,
            `billing_postcode` varchar(20) default NULL,
            `billing_state` varchar(64) default NULL,
            `billing_country` varchar(64) default NULL,
            `billing_address_format_id` int(5) default '0',
            `payment_method` varchar(128) NOT NULL default '',
            `payment_method_code` varchar(32) default NULL,
            `shipping_method` varchar(128) default NULL,
            `shipping_method_code` varchar(32) default NULL,
            `coupon_code` varchar(32) default NULL,
            `date_modified_on` datetime default NULL,
            `date_purchased_on` datetime NOT NULL,
            `date_finished_on` datetime default NULL,
            `order_status_id` mediumint(8) unsigned default NULL,
            `currency` char(3) NOT NULL,
            `currency_rate` decimal(17,10) UNSIGNED NOT NULL DEFAULT '1.0000',
            `order_total` decimal(14,2) NOT NULL,
            `txn_id` int(11) NOT NULL default '0',
            `ip_address` varchar(96) default NULL,
            `site_id` smallint(5) unsigned NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `INDEX_SALES_ORDER` USING BTREE(`order_status_id`,`id`,`customer_id`),
            KEY `INDEX_SALES_ORDER_DATE` USING BTREE (`date_purchased_on`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_creditcard')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_creditcard')}` (
          `order_id` int(10) unsigned NOT NULL,
          `cc_type` varchar(64) NOT NULL,
          `cc_owner` varchar(64) DEFAULT NULL,
          `cc_number` varchar(64) NOT NULL,
          `cc_expires_year` varchar(20) NOT NULL,
          `cc_expires_month` varchar(20) NOT NULL,
          `cc_issue_year` varchar(20) DEFAULT NULL,
          `cc_issue_month` varchar(20) DEFAULT NULL,
          `cc_cvv` varchar(20) DEFAULT NULL,
          PRIMARY KEY  USING BTREE (`order_id`),
          CONSTRAINT `FK_SALES_ORDER_CREDITCARD_ORDER` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_order')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_product')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_product')}` (
            `id` int(11) NOT NULL auto_increment,
            `order_id` int(10) unsigned NOT NULL,
            `product_id` int(10) unsigned DEFAULT NULL,
            `variation_id` int(11) DEFAULT NULL,
            `sku` varchar(128) NOT NULL,
            `name` varchar(128) NOT NULL,
            `price` decimal(15,4) NOT NULL,
            `final_price` decimal(15,4) NOT NULL,
            `final_weight` decimal(10,4) NOT NULL DEFAULT '0.0000',
            `tax` decimal(7,4) NOT NULL DEFAULT '0.0000',
            `quantity` decimal(15,4) NOT NULL,
            `backorder` int(1) NOT NULL DEFAULT '0',
            PRIMARY KEY  USING BTREE (`id`),
            KEY `INDEX_SALES_ORDER_PRODUCT` (`order_id`),
            CONSTRAINT `FK_SALES_ORDER_PRODUCT` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_order')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_product_attribute')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_product_attribute')}` (
            `id` int(11) NOT NULL auto_increment,
            `order_product_id` int(11) NOT NULL,
            `product_option` varchar(128) NOT NULL,
            `product_option_value` varchar(128) NOT NULL,
            PRIMARY KEY  USING BTREE (`id`),
            KEY `FK_SALES_ORDER_PRODUCT_ATTRIBUTE` (`order_product_id`),
            CONSTRAINT `FK_SALES_ORDER_PRODUCT_ATTRIBUTE` FOREIGN KEY (`order_product_id`) REFERENCES `{$installer->getTable('sales_order_product')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_status')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_status')}` (
            `id` smallint(5) NOT NULL auto_increment,
            `name` char(32) NOT NULL,
            `system` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10;

        INSERT INTO `{$installer->getTable('sales_order_status')}` (`id`, `name`, `system`) VALUES
        (0, 'new', 1),
        (1, 'pending', 1),
        (2, 'processing', 1),
        (3, 'ship', 1),
        (4, 'delivered', 1),
        (5, 'complete', 1),
        (6, 'hold', 1),
        (7, 'cancel', 1),
        (8, 'refund', 1),
        (9, 'failed', 1);

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_status_relation')}`;
        CREATE TABLE  `{$installer->getTable('sales_order_status_relation')}` (
          `from_status` smallint(5) NOT NULL,
          `to_status` smallint(5) NOT NULL,
          PRIMARY KEY  (`from_status`,`to_status`),
          KEY `INDEX_SALES_ORDER_STATUS_RELATION_FROM` (`from_status`),
          KEY `INDEX_SALES_ORDER_STATUS_RELATION_TO` (`to_status`),
          CONSTRAINT `FK_SALES_ORDER_STATUS_RELATION_TO` FOREIGN KEY (`to_status`) REFERENCES `{$installer->getTable('sales_order_status')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `FK_SALES_ORDER_STATUS_RELATION_FROM` FOREIGN KEY (`from_status`) REFERENCES `{$installer->getTable('sales_order_status')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('sales_order_status_relation')}` (`from_status`, `to_status`) VALUES
        (0, 1), (0, 9), (1, 2),
        (1, 6), (1, 7), (1, 9),
        (2, 3), (2, 6), (2, 7),
        (2, 8), (2, 9), (3, 4),
        (3, 8), (3, 9), (4, 5),
        (4, 8), (6, 1), (6, 2),
        (6, 7)
        ;


        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_status_history')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_status_history')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `order_id` int(10) unsigned NOT NULL,
            `order_status_id` smallint(5) unsigned NOT NULL,
            `created_on` datetime NOT NULL,
            `notified` tinyint(1) unsigned NOT NULL,
            `comments` text,
            PRIMARY KEY  (`id`),
            KEY `INDEX_SALES_ORDER_STATUS_HISTORY` (`order_id`,`order_status_id`),
            CONSTRAINT `FK_SALES_ORDER_STATUS_HISTORY_ORDER` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_order')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_total')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_total')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `order_id` int(10) unsigned NOT NULL,
            `code` VARCHAR(32)  NOT NULL,
            `title` varchar(128) NOT NULL,
            `value` decimal(15,4) NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `INDEX_SALES_ORDER_TOTAL` (`order_id`),
            CONSTRAINT `FK_SALES_ORDER_TOTAL_ORDER` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_order')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sales_order_status_text')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sales_order_status_text')}` (
            `status_id` smallint(5) NOT NULL,
            `language_id` smallint(5) unsigned NOT NULL,
            `status_name` varchar(128) NOT NULL,
            PRIMARY KEY  (`status_id`,`language_id`),
            KEY `INDEX_SALES_ORDER_STATUS_TEXT_LANGUAGE` (`language_id`),
            CONSTRAINT `FK_SALES_ORDER_STATUS_TEXT_STATUS` FOREIGN KEY (`status_id`) REFERENCES `{$installer->getTable('sales_order_status')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_SALES_ORDER_STATUS_TEXT_LANGUAGE` FOREIGN KEY (`language_id`) REFERENCES `{$installer->getTable('locale_language')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        ");

        $statusText = array(
            'New',
            'Pending payment',
            'Proccesing',
            'Shipping',
            'Delivered',
            'Completed',
            'Holded',
            'Canceled',
            'Refund',
            'Failed'
        );
        $mStatusText = Axis::model('sales/order_status_text');
        $languages = Axis_Collect_Language::collect();
        foreach (Axis::model('sales/order_status')->fetchAll() as $status) {
            foreach ($languages as $langId => $langName) {
                $mStatusText->createRow(array(
                    'status_id'     => $status->id,
                    'language_id'   => $langId,
                    'status_name'   => $statusText[$status->id]
                ))->save();
            }
        }

        Axis::single('core/config_field')
            ->add('sales', 'Sales', null, null, array('translation_module' => 'Axis_Sales'))
            ->add('sales/order/defaultStatusId', 'Sales/Order/Default Order Status', 1, 'select', 'Default Order Status', array('config_options' => '{"1":"pending", "2":"processing"}'))
            ->add('sales/order/order_number_pattern_prefix', 'Prefix for Custom Order Number', '')
            ->add('sales/order/order_number_pattern', 'Pattern for Custom Order Number', '100000000', 'Please notice: Changing code pattern for existing orders in database can cause problems.')
            ->add('sales/order/email', 'Order notifications reciever', 'email1', 'select', 'All notifications about new orders will be sended to this email', array('model' => 'MailBoxes'));

        Axis::single('admin/menu')
            ->add('Localization', null, 80, 'Axis_Locale')
            ->add('Localization->Order statuses', 'sales_order-status', 30, 'Axis_Sales')
            ->add('Sales', null, 30, 'Axis_Sales')
            ->add('Sales->Orders', 'sales_order', 10);

        Axis::single('admin/acl_resource')
            ->add('admin/sales', 'Sales')
            ->add('admin/sales_order', 'Orders')
            ->add("admin/sales_order/delete")
            ->add("admin/sales_order/get-order-info")
            ->add("admin/sales_order/index")
            ->add("admin/sales_order/list")
            ->add("admin/sales_order/print")
            ->add("admin/sales_order/set-status")
            ->add('admin/sales_order-status', 'Orders Statusses')
            ->add("admin/sales_order-status/batch-save")
            ->add("admin/sales_order-status/delete")
            ->add("admin/sales_order-status/get-childs")
            ->add("admin/sales_order-status/get-info")
            ->add("admin/sales_order-status/index")
            ->add("admin/sales_order-status/list")
            ->add("admin/sales_order-status/save");
    }

    public function down()
    {

    }
}