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
 * @package     Axis_ShippingUsps
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingUsps_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('shipping', 'Shipping Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('shipping/Usps_Standard',           'Shipping Methods/Usps Standard', null, null, array('translation_module' => 'Axis_ShippingUsps'))
            ->add('shipping/Usps_Standard/enabled',   'Shipping Methods/Usps Standard/Enabled', '0', 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('shipping/Usps_Standard/taxClass',  'Tax Class', '', 'select', 'Tax class that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Class', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Usps_Standard/taxBasis',  'Tax Basis', '', 'select', 'Address that will be used for tax calculation', array('model' => 'Axis_Tax_Model_Option_Basis', 'translation_module' => 'Axis_Tax'))
            ->add('shipping/Usps_Standard/geozone',   'Allowed Shipping Zone', '1', 'select', 'Shipping method will be available only for selected zone', array('model' => 'Axis_Location_Model_Option_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Usps_Standard/handling',  'Handling price', '5')
            ->add('shipping/Usps_Standard/sortOrder', 'Sort Order', '0', 'string', array('translation_module' => 'Axis_Core'))
            ->add('shipping/Usps_Standard/payments',  'Disallowed Payments', '0', 'multiple', 'Selected payment methods will be not available with this shipping method', array('model' => 'Axis_Checkout_Model_Option_Payment', 'translation_module' => 'Axis_Admin'))
            ->add('shipping/Usps_Standard/title',     'Title', 'United States Postal Service')

            ->add('shipping/Usps_Standard/gateway',        'Gateway Url', 'http://production.shippingapis.com/ShippingAPI.dll')
            ->add('shipping/Usps_Standard/service',        'Allowed Service', Axis_ShippingUsps_Model_Option_Standard_Service::getDeafult(), 'multiple', array('model' => 'Axis_ShippingUsps_Model_Option_Standard_Service'))
            ->add('shipping/Usps_Standard/userId',         'User ID', '', 'string', '', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('shipping/Usps_Standard/container',      'Container', Axis_ShippingUsps_Model_Option_Standard_Package::VARIABLE, 'select', '', array('model' => 'Axis_ShippingUsps_Model_Option_Standard_Package'))
            ->add('shipping/Usps_Standard/size',           'Size', Axis_ShippingUsps_Model_Option_Standard_Size::REGULAR, 'select', '', array('model' => 'Axis_ShippingUsps_Model_Option_Standard_Size'))
            ->add('shipping/Usps_Standard/machinable',     'Machinable', '1', 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean'))
            ->add('shipping/Usps_Standard/allowedMethods', 'Allowed Shipping Methods', Axis_ShippingUsps_Model_Option_Standard_ServiceLabel::getDeafult(), 'multiple', array('model' => 'Axis_ShippingUsps_Model_Option_Standard_ServiceLabel'))
            ;
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('shipping/Usps_Standard');
        Axis::single('core/config_field')->remove('shipping/Usps_Standard');
    }
}