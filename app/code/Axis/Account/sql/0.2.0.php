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

class Axis_Account_Upgrade_0_2_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.0';
    protected $_info = 'Address form fields customization';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('account/address_form/company_status', 'Account/Address Form/Company Status', 'optional', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/company_sort_order', 'Account/Address Form/Company Sort Order', 10)

            ->add('account/address_form/phone_status', 'Account/Address Form/Phone Status', 'required', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/phone_sort_order', 'Account/Address Form/Company Sort Order', 20)

            ->add('account/address_form/fax_status', 'Account/Address Form/Fax Status', 'optional', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/fax_sort_order', 'Account/Address Form/Company Sort Order', 30)

            ->add('account/address_form/street_address_status', 'Account/Address Form/Street Status', 'required', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/street_address_sort_order', 'Account/Address Form/Company Sort Order', 40)

            ->add('account/address_form/city_status', 'Account/Address Form/City Status', 'required', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/city_sort_order', 'Account/Address Form/Company Sort Order', 50)

            ->add('account/address_form/zone_id_status', 'Account/Address Form/Region Status', 'required', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/zone_id_sort_order', 'Account/Address Form/Company Sort Order', 60)

            ->add('account/address_form/postcode_status', 'Account/Address Form/Postcode Status', 'required', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/postcode_sort_order', 'Account/Address Form/Company Sort Order', 70)

            ->add('account/address_form/country_id_status', 'Account/Address Form/Country Status', 'required', 'select', array('model' => 'AddressFieldStatus'))
            ->add('account/address_form/country_id_sort_order', 'Account/Address Form/Company Sort Order', 80);
    }

    public function down()
    {
        //
    }
}
