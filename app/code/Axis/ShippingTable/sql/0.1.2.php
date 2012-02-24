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


        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Table_Standard', 'Shipping Methods/Table Standard', null, null, array('translation_module' => 'Axis_ShippingTable'))
            ->add('shipping/Table_Standard/enabled', 'Shipping Methods/Table Standard/Enabled', '0', 'bool', '', array('model'=> 'Axis_Core_Model_Config_Value_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('shipping/Table_Standard/geozone', 'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Axis_Location_Model_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Table_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Table_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Table_Standard/sortOrder', 'Sort Order', '0', 'string', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Table_Standard/handling', 'Handling Fee', '0')
            ->add('shipping/Table_Standard/type', 'Table Method', Axis_ShippingTable_Model_Standard_Service::PER_PRICE, 'select', 'The shipping cost is based on the order total or the total weight of the items ordered or the total number of items orderd.', array('model' => 'Axis_ShippingTable_Model_Standard_Service'))
            ->add('shipping/Table_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Axis_Checkout_Model_Payment', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Table_Standard/formDesc', 'Checkout Description', 'Table Rate');
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('shippingtable_rate')}`;
        ");

        Axis::single('core/config_value')->remove('shipping/Table_Standard');
        Axis::single('core/config_field')->remove('shipping/Table_Standard');
    }
}