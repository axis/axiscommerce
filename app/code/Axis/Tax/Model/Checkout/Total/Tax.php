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
 * @package     Axis_Tax
 * @subpackage  Axis_Tax_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tax
 * @subpackage  Axis_Tax_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tax_Model_Checkout_Total_Tax extends Axis_Checkout_Model_Total_Abstract
{
    protected $_code = 'tax';
    protected $_title = 'Tax';

    public function collect(Axis_Checkout_Model_Total $total)
    {
        $checkout = Axis::single('checkout/checkout');
        $taxBasis = null;

        if (null !== $checkout->shipping()) {
            $taxBasis = $checkout->shipping()->_config->taxBasis;
        }

        if (!$taxBasis && !$taxBasis = Axis::config()->tax->main->taxBasis) {
            return false;
        }

        $address = $checkout->getStorage()->{$taxBasis};
        if (!$address || !$address->hasCountry()) {
            return false;
        }

        $countryId = $address->country->id;
        $zoneId = $address->hasZone() && $address->zone->hasId() ?
            $address->zone->id : null;

        $geozoneIds = Axis::single('location/geozone')
            ->getIds($countryId, $zoneId);

        $customerGroupId = Axis::single('account/customer')->getGroupId();

        $tax = Axis::single('tax/rate')->calculateByCartId(
            $checkout->getCart()->getCartId(), $geozoneIds, $customerGroupId
        );

        $total->addCollect(array(
            'code'      => $this->getCode(),
            'title'     => $this->getTitle(),
            'total'     => $tax,
            'sortOrder' => $this->_config->sortOrder
        ));
    }
}