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
class Axis_Checkout_Model_Total_Tax extends Axis_Checkout_Model_Total_Abstract
{
    protected $_code = 'tax';
    protected $_title = 'Tax';

    public function collect(Axis_Checkout_Model_Total $total)
    {
        $checkout = Axis::single('checkout/checkout');
        
        if (null === $checkout->shipping()) {
            return false;
        } 
        
        if (!$taxBasis = $checkout->shipping()->_config->taxBasis) {
            if (!$taxBasis = Axis::config()->tax->main->taxBasis) {
                return false;
            }
        }
        $address = $checkout->getStorage()->{$taxBasis};

        $countryId = $address->country->id;
        $zoneId = $address->hasZone() && $address->zone->hasId() ?
            $address->zone->id : null;
        
        $geozoneIds = Axis::single('location/geozone')
            ->getIds($countryId, $zoneId);

        $customerId = Axis::getCustomerId();
        $customerGroupId = Axis::single('account/customer')
            ->getGroupId($customerId);

        $tax = Axis::single('tax/rate')->calculateByCartId(
            $checkout->getCart()->getCartId(), $geozoneIds, $customerGroupId
        );

        $total->addCollect(array(
            'code'  => $this->getCode(),
            'title' => $this->getTitle(),
            'total' => $tax,
            'sortOrder' => $this->_config->sortOrder
        ));
    }
}