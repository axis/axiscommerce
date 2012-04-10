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
 * @package     Axis_ShippingFedex
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingFedex_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Fedex_Standard', 'Shipping Methods/Fedex', null, null, array('translation_module' => 'Axis_ShippingFedex'))
            ->add('shipping/Fedex_Standard/enabled', 'Shipping Methods/Fedex/Enabled', '0', 'radio', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('shipping/Fedex_Standard/geozone', 'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Axis_Location_Model_Option_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Fedex_Standard/taxBasis', 'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Fedex_Standard/taxClass', 'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Fedex_Standard/sortOrder', 'Sort Order', '0', 'text', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Fedex_Standard/payments', 'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Axis_Checkout_Model_Option_Payment', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Fedex_Standard/title', 'Title', 'Fedex Express')
            ->add('shipping/Fedex_Standard/account', 'Account Id', '', 'text', '', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('shipping/Fedex_Standard/package', 'Package', Axis_ShippingFedex_Model_Option_Standard_Package::YOUR_PACKAGING, 'select', '', array('model' => 'Axis_ShippingFedex_Model_Option_Standard_Package'))
            ->add('shipping/Fedex_Standard/dropoff', 'Dropoff', Axis_ShippingFedex_Model_Option_Standard_Pickup::REGULAR_PICKUP, 'select', '', array('model' => 'Axis_ShippingFedex_Model_Option_Standard_Pickup'))
            ->add('shipping/Fedex_Standard/allowedTypes', 'Allowed methods', Axis_ShippingFedex_Model_Option_Standard_Service::FEDEX_GROUND, 'multiple', '', array('model' => 'Axis_ShippingFedex_Model_Option_Standard_Service'))
            ->add('shipping/Fedex_Standard/measure', 'UPS Weight Unit', Axis_ShippingFedex_Model_Option_Standard_Measure::LBS, 'select', 'LBS or KGS', array('model' => 'Axis_ShippingFedex_Model_Option_Standard_Measure'))
            ->add('shipping/Fedex_Standard/residenceDelivery', 'Residential Delivery', '0', 'radio', '', array('model'=> 'Axis_Core_Model_Option_Boolean'))
            ->add('shipping/Fedex_Standard/gateway', 'Fedex Gateway Url', 'https://gateway.fedex.com/GatewayDC')
            ->add('shipping/Fedex_Standard/handling', 'Handling Fee', '0', 'text', '')
        ;
    }

    public function down()
    {
        //Axis::single('core/config_value')->remove('shipping/Fedex_Express');
        //Axis::single('core/config_field')->remove('shipping/Fedex_Express');
        //Axis::single('core/config_value')->remove('shipping/Fedex_Ground');
        //Axis::single('core/config_field')->remove('shipping/Fedex_Ground');

        Axis::single('core/config_value')->remove('shipping/Fedex_Standard');
        Axis::single('core/config_field')->remove('shipping/Fedex_Standard');
    }
}