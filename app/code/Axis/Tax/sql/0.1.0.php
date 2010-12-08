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
 * @package     Axis_Tax
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Tax_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('tax_class')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('tax_class')}` (
            `id` mediumint(8) unsigned NOT NULL auto_increment,
            `name` varchar(128) NOT NULL,
            `description` text,
            `created_on` datetime NOT NULL,
            `modified_on` datetime default NULL,
            PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        INSERT INTO `{$installer->getTable('tax_class')}` (`id`, `name`, `description`) VALUES (1, 'Taxable Goods', 'Taxable Goods'), (2, 'Shipping', 'Tax shipping');

        -- DROP TABLE IF EXISTS `{$installer->getTable('tax_rate')}`;
        CREATE TABLE  `{$installer->getTable('tax_rate')}` (
          `id` mediumint(8) unsigned NOT NULL auto_increment,
          `tax_class_id` mediumint(8) unsigned NOT NULL,
          `geozone_id` mediumint(8) unsigned NOT NULL,
          `rate` decimal(9,4) NOT NULL,
          `description` varchar(255) NOT NULL,
          `created_on` datetime NOT NULL,
          `modified_on` datetime default NULL,
          `customer_group_id` smallint(5) unsigned NOT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `UNIQUE_tax_class` USING BTREE (`tax_class_id`,`geozone_id`,`customer_group_id`),
          KEY `FK_tax_customer_group` (`customer_group_id`),
          KEY `FK_tax_geozone` USING BTREE (`geozone_id`),
          KEY `FK_tax_rate_class` (`tax_class_id`),
          CONSTRAINT `FK_tax_customer_group` FOREIGN KEY (`customer_group_id`) REFERENCES `{$installer->getTable('account_customer_group')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_tax_geozone` FOREIGN KEY (`geozone_id`) REFERENCES `{$installer->getTable('location_geozone')}` (`id`) ON DELETE CASCADE,
          CONSTRAINT `FK_tax_rate_class` FOREIGN KEY (`tax_class_id`) REFERENCES `{$installer->getTable('tax_class')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

        INSERT INTO `{$installer->getTable('tax_rate')}` (`id`, `tax_class_id`, `geozone_id`, `customer_group_id`, `rate`, `description`) 
        VALUES (1, 1, 2, ". Axis_Account_Model_Customer_Group::GROUP_ALL_ID . ", 8, 'Taxable Goods'),
               (2, 2, 2," . Axis_Account_Model_Customer_Group::GROUP_ALL_ID . ", 8, 'Shipping Tax');

        ");

        Axis::single('core/config_field')
            ->add('tax', 'Tax', null, null, array('translation_module' => 'Axis_Tax'))
            ->add('tax/main/taxBasis', 'Tax/General/TaxBasis', 'delivery', 'select', 'Address that will be used for tax calculation', array('model' => 'TaxBasis'))
            ->add('tax/shipping/taxBasis', 'Tax/Shipping Tax/Shipping TaxBasis', 'delivery', 'select', 'Address that will be used for shipping tax calculation', array('model' => 'TaxBasis'))
            ->add('tax/shipping/taxClass', 'Shipping TaxClass', '1', 'select', 'Tax class that will be used for shipping tax calculation', array('model' => 'TaxClass'));

        Axis::single('admin/menu')
            ->add('Locations / Taxes', null, 70, 'Axis_Location')
            ->add('Locations / Taxes->Tax Classes', 'tax_class', 40, 'Axis_Tax')
            ->add('Locations / Taxes->Tax Rates', 'tax_rate', 50, 'Axis_Tax');

        Axis::single('admin/acl_resource')
            ->add('admin/tax', 'Tax')
            ->add('admin/tax_class', 'Tax Classes')
            ->add("admin/tax_class/delete")
            ->add("admin/tax_class/index")
            ->add("admin/tax_class/list")
            ->add("admin/tax_class/save")

            ->add('admin/tax_rate', 'Tax Rates')
            ->add("admin/tax_rate/delete")
            ->add("admin/tax_rate/index")
            ->add("admin/tax_rate/list")
            ->add("admin/tax_rate/save");
    }

    public function down()
    {

    }
}