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
 * @package     Axis_Checkout
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_2_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.0';
    protected $_info = 'Default values for checkout form added';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('checkout', 'Checkout', null, null, array('translation_module' => 'Axis_Checkout'))
            ->add('checkout/address_form/custom_fields_display_mode', 'Checkout/Address Form/Display Mode for Custom Fields', Axis_Checkout_Model_Form_Address_CustomFieldsDisplayMode::getConfigOptionDeafultValue(), 'select', array('model' => 'Axis_Checkout_Model_Form_Address_CustomFieldsDisplayMode'))
            ->add('checkout/address_form/shipping_address_enabled', 'Display Shipping Address', 1, 'bool', '', array('model'=> 'Axis_Core_Model_Config_Value_Boolean'))
            ->add('checkout/default_values', 'Checkout/Default Values', null, null, array('translation_module' => 'Axis_Checkout'))
            ->add('checkout/default_values/country_id', 'Checkout/Default Values/Country', 223, 'select', array('model' => 'Axis_Location_Model_Option_Country', 'translation_module' => 'Axis_Account'))
            ->add('checkout/default_values/zone_id', 'State(Region) Id', 12, 'string', 'You can get the id of desired region at admin/location_zone', array('translation_module' => 'Axis_Account'))
            ->add('checkout/default_values/postcode', 'Postcode', 90064, 'string', array('translation_module' => 'Axis_Account'))
            ->add('checkout/default_values/shipping_method', 'Shipping Method', 'Flat_Standard_standard', 'string')
            ->add('checkout/default_values/payment_method', 'Payment Method', 'CashOnDelivery_Standard', 'select', array('model' => 'Axis_Checkout_Model_Payment'));
    }
}