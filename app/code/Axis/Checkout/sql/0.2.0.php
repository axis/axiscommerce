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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_2_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.0';
    protected $_info = 'Default values for checkout form added';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('checkout', 'Checkout')
                ->section('address_form', 'Address Form')
                    ->option('custom_fields_display_mode', 'Display Mode for Custom Fields')
                        ->setValue(Axis_Checkout_Model_Option_Form_Address_CustomFieldsDisplayMode::getDeafult())
                        ->setType('select')
                        ->setModel('checkout/option_form_address_customFieldsDisplayMode')
                    ->option('shipping_address_enabled', 'Display Shipping Address', true)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                ->section('/address_form')

                ->section('default_values', 'Default Values')
                    ->setTranslation('Axis_Checkout')
                    ->option('country_id', 'Country', 223)
                        ->setType('select')
                        ->setModel('location/option_country')
                        ->setTranslation('Axis_Account')
                    ->option('zone_id', 'State(Region) Id', 12)
                        ->setDescription('You can get the id of desired region at admin/location_zone')
                        ->setTranslation('Axis_Account')
                    ->option('postcode', 'Postcode', 90064)
                        ->setTranslation('Axis_Account')
                    ->option('shipping_method', 'Shipping Method', 'Flat_Standard_standard')
                    ->option('payment_method', 'Payment Method', 'CashOnDelivery_Standard')
                        ->setType('select')
                        ->setModel('checkout/option_default_payment')

            ->section('/');
    }
}