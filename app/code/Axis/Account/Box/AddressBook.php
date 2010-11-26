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
 * @copyright   Copyright 2008-2010 Axis
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
        $customerId = Axis::getCustomerId();
        $addressList = Axis::single('account/customer_address')
           ->getSortListByCustomerId($customerId);
        $customer = Axis::single('account/customer')
            ->find($customerId)
            ->current();
        
        if (!$customerId || !count($addressList)) {
            return false;
        }
        $data = array();
        foreach ($addressList as $address) {
            if ($address->id  == $customer->default_shipping_address_id) {
                $data['delivery'] = $address;
            }
            if ($address->id  == $customer->default_billing_address_id) {
                $data['billing'] = $address;
            }
        }
        
        $this->updateData($data);
    }
    
    public function hasContent()
    {
        return $this->hasDelivery() || $this->hasBilling();
    }
}