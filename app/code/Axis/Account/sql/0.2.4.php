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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Account_Upgrade_0_2_4 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.4';
    protected $_info = 'Default values address form added';

    public function up()
    {
        Axis::single('core/config_builder')
            ->section('account')
                ->section('address_form')
                    ->option('company_value', 'Company Default Value')
                    ->option('phone_value', 'Phone Default Value')
                    ->option('fax_value', 'Fax Default Value')
                    ->option('street_address_value', 'Street Address Default Value')
                    ->option('city_value', 'City Default Value')
                    ->option('zone_id_value', 'State(Region) Default Value', 12)
                        ->setDescription('You can get the id of desired region at [admin]/location/zone')
                    ->option('postcode_value', 'Postcode Default Value', 90064)
                    ->option('country_id_value', 'Country Default Value', 223)
                        ->setType('select')
                        ->setModel('location/option_country')
            ->section('/')
        ;
    }
}