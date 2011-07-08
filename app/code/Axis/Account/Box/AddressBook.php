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
 * @subpackage  Axis_Account_Box
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Box_AddressBook extends Axis_Account_Box_Abstract
{
    protected $_title = 'My Addresses';
    protected $_class = 'box-address';

    public function init()
    {
        if (!$customerId = Axis::getCustomerId()) {
            return false;
        }
        $addressList = Axis::single('account/customer_address')
           ->getSortListByCustomerId($customerId);

        if (!count($addressList)) {
            return false;
        }

        $customer = Axis::getCustomer();
        $data = array();
        foreach ($addressList as $address) {
            if ($address->id  == $customer->default_shipping_address_id) {
                $data['delivery'] = $address;
            }
            if ($address->id  == $customer->default_billing_address_id) {
                $data['billing'] = $address;
            }
        }

        $this->setFromArray($data);
        return true;
    }

    protected function _beforeRender()
    {
        return $this->hasDelivery() || $this->hasBilling();
    }
}