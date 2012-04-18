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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Account_Upgrade_0_2_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.0';
    protected $_info = 'Address form fields customization';

    public function up()
    {
        $required = Axis_Core_Model_Option_Config_Field_Status::REQUIRED;
        $optional = Axis_Core_Model_Option_Config_Field_Status::OPTIONAL;
        
        Axis::single('core/config_builder')
            ->section('account', 'Account')
                ->section('address_form', 'Address Form')
                    ->option('company_status', 'Company Status', $optional)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('company_sort_order', 'Company Sort Order', 10)
                    ->option('phone_status', 'Phone Status', $required)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('phone_sort_order', 'Company Sort Order', 20)
                    ->option('fax_status', 'Fax Status', $optional)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('fax_sort_order', 'Company Sort Order', 30)
                    ->option('street_address_status', 'Street Status', $required)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('street_address_sort_order', 'Company Sort Order', 40)
                    ->option('city_status', 'City Status', $required)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('city_sort_order', 'Company Sort Order', 50)
                    ->option('zone_id_status', 'Region Status', $required)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('zone_id_sort_order', 'Company Sort Order', 60)
                    ->option('postcode_status', 'Postcode Status', $required)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('postcode_sort_order', 'Company Sort Order', 70)
                    ->option('country_id_status', 'Country Status', $required)
                        ->setType('select')
                        ->setModel('core/option_config_field_status')
                    ->option('country_id_sort_order', 'Company Sort Order', 80)

            ->section('/');
    }
}
