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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Tax_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

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
        VALUES (1, 1, 1, ". Axis_Account_Model_Customer_Group::GROUP_ALL_ID . ", 8, 'Taxable Goods'),
               (2, 2, 1," . Axis_Account_Model_Customer_Group::GROUP_ALL_ID . ", 8, 'Shipping Tax');

        ");

        $this->getConfigBuilder()
            ->section('tax', 'Tax')
                ->setTranslation('Axis_Tax')
                ->section('main', 'General')
                    ->option('taxBasis', 'TaxBasis')
                        ->setValue(Axis_Tax_Model_Option_Basis::SHIPPING)
                        ->setType('select')
                        ->setDescription('Address that will be used for tax calculation')
                        ->setModel('tax/option_basis')
                ->section('/main')
                ->section('shipping', 'Shipping Tax')
                    ->option('taxBasis', 'Shipping TaxBasis')
                        ->setValue(Axis_Tax_Model_Option_Basis::SHIPPING)
                        ->setType('select')
                        ->setDescription('Address that will be used for shipping tax calculation')
                        ->setModel('tax/option_basis')
                    ->option('taxClass', 'Shipping TaxClass', 1)
                        ->setType('select')
                        ->setDescription('Tax class that will be used for shipping tax calculation')
                        ->setModel('tax/option_class')

            ->section('/');
    }
}