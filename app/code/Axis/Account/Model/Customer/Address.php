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
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_Address extends Axis_Db_Table
{
    protected $_name = 'account_customer_address';
    protected $_selectClass = 'Axis_Account_Model_Customer_Address_Select';

    /**
     * Return full address with country & zone data
     *
     * @param mixed int|array $address
     * @return Axis_Address
     */
    public function getAddress($address)
    {
        if (is_array($address)) {
            $countryId  = isset($address['country_id']) ?
                $address['country_id'] : false;
            $zoneId     = isset($address['zone_id']) ?
                $address['zone_id'] : false;
        } else {
            $address = $this->find($address)->current();
            if (!$address instanceof Axis_Db_Table_Row) {
                return false;
            }
            $countryId = $address->country_id;
            $zoneId    = $address->zone_id ? $address->zone_id : false;
            $address   = $address->toArray();
        }

        if ($countryId) {
            $address['country'] = Axis::single('location/country')
                ->find($countryId)
                ->current()
                ->toArray();
        }
        if ($zoneId) {
            $address['zone'] = Axis::single('location/zone')
                ->find($zoneId)
                ->current()
                ->toArray();
        }
        return new Axis_Address($address);
    }

    /**
     *
     * @param int $customerId
     * @return array of Axis_Address object
     */
    public function getSortListByCustomerId($customerId = null)
    {
        if (null === $customerId) {
            $customerId = Axis::getCustomerId();
        }
        if (null === $customerId) {
            return array();
        }

        $addressList = $this->select()
            ->from('account_customer_address')
            ->where('customer_id = ?', $customerId)
            ->addCountry()
            ->addZone()
            ->fetchAll();

        foreach ($addressList as &$address) {
            foreach ($address as $key => $value)  {
                list($prefix,) = explode('_', $key, 2);
                if ($key === $prefix
                    || !in_array($prefix, array('country', 'zone'))) {

                    continue;
                }
                $address[$prefix][str_replace($prefix . '_', '', $key)] = $value;
                unset($address[$key]);
            }
        }

        $defaultBillingId = false;
        $defaultShippingId = false;
        if ($customer = Axis::single('account/customer')->find($customerId)->current()) {
            $defaultBillingId = $customer->default_billing_address_id;
            $defaultShippingId = $customer->default_shipping_address_id;
        }

        $j = 0;
        for ($i = 0, $n = count($addressList); $i < $n; $i++) {
            if ($addressList[$i]['id'] != $defaultBillingId
                && $addressList[$i]['id'] != $defaultShippingId) {

                continue;
            }

            if ($addressList[$i]['id'] == $defaultShippingId) {
                $addressList[$i]['default_shipping'] = 1;
            }
            if ($addressList[$i]['id'] == $defaultBillingId) {
                $addressList[$i]['default_billing'] = 1;
            }
            list($addressList[$i], $addressList[$j]) =
                array($addressList[$j], $addressList[$i]);

            ++$j;
        }
        foreach ($addressList as &$address) {
            $address = new Axis_Address($address);
        }
        return $addressList;
    }
}