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
 * @package     Axis_ShippingTable
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingTable_Upgrade_0_1_2 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.2';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('shippingtable_rate')}`;
        CREATE TABLE  `{$installer->getTable('shippingtable_rate')}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `site_id` smallint(5) NOT NULL default '0',
            `country_id` mediumint(8) NOT NULL default '0',
            `zone_id` mediumint(8) NOT NULL default '0',
            `zip` varchar(10) NOT NULL,
            `value` decimal(12,4) NOT NULL default '0.0000',
            `price` decimal(12,4) NOT NULL default '0.0000',
            PRIMARY KEY  USING BTREE (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8

        ");
        
        
        Axis::single('core/config_builder')
            ->section('shipping', 'Shipping Methods')
                ->setTranslation('Axis_Admin')
                ->section('Table_Standard', 'Table Standard')
                    ->setTranslation('Axis_ShippingTable')
                    ->option('enabled', 'Enabled')
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('geozone', 'Allowed Shipping Zone', '1')
                        ->setType('select')
                        ->setDescription('Shipping method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('taxBasis', 'Tax Basis')
                        ->setType('select')
                        ->setDescription('Address that will be used for tax calculation')
                        ->setModel('tax/option_basis')
                        ->setTranslation('Axis_Tax')
                    ->option('taxClass', 'Tax Class')
                        ->setType('select')
                        ->setDescription('Tax class that will be used for tax calculation')
                        ->setModel('tax/option_class')
                        ->setTranslation('Axis_Tax')
                    ->option('sortOrder', 'Sort Order')
                        ->setTranslation('Axis_Core')
                    ->option('handling', 'Handling Fee')
                    ->option('type', 'Table Method', Axis_ShippingTable_Model_Option_Standard_Service::PER_PRICE)
                        ->setType('select')
                        ->setDescription('The shipping cost is based on the order total or the total weight of the items ordered or the total number of items orderd.')
                        ->setModel('shippingTable/option_standard_service')
                    ->option('payments', 'Disallowed Payments')
                        ->setType('multiple')
                        ->setDescription('Selected payment methods will be not available with this shipping method')
                        ->setModel('checkout/option_payment')
                        ->setTranslation('Axis_Admin')
                    ->option('formDesc', 'Checkout Description', 'Table Rate')

            ->section('/');

    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('shippingtable_rate')}`;
        ");

        Axis::single('core/config_builder')->remove('shipping/Table_Standard');
    }
}