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
 * @subpackage  Axis_Checkout_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Total_ShippingTax extends Axis_Checkout_Model_Total_Abstract
{
    protected $_code = 'shipping_tax';
    protected $_title = 'Shipping Tax';

    public function collect(Axis_Checkout_Model_Total $total)
    {
        $checkout = Axis::single('checkout/checkout');

        if (null === $checkout->shipping()) {
            return false;
        }

        if (!$taxClassId = $checkout->shipping()->config()->taxClass) {
            if (!$taxClassId = Axis::config()->tax->shipping->taxClass) {
                return false;
            }
        }

        if (!$taxBasis = $checkout->shipping()->config()->taxBasis) {
            if (!$taxBasis = Axis::config()->tax->shipping->taxBasis) {
                return false;
            }
        }

        $address = $checkout->getStorage()->{$taxBasis};
        $countryId = $address->country->id;
        $zoneId = $address->hasZone() && $address->zone->hasId() ?
            $address->zone->id : null;
        $geozoneIds = Axis::single('location/geozone')->getIds(
            $countryId, $zoneId
        );
        if (!count($geozoneIds)) {
            return false;
        }

        $customerGroupId = Axis::single('account/customer')->getGroupId();
        if (!$customerGroupId) {
            return false;
        }
        $type = $checkout->shipping()->getType(
            $checkout->getShippingRequest(), $checkout->getShippingMethodCode()
        );
        $tax = Axis::single('tax/rate')->calculateByPrice(
            $type['price'], $taxClassId, $geozoneIds, $customerGroupId
        );

        $total->addCollect(array(
            'code'  => $this->getCode(),
            'title' => $this->getTitle(),
            'total' => $tax,
            'sortOrder' => $this->_config->sortOrder
        ));
    }
}