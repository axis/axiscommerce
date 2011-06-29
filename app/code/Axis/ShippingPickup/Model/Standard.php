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
 * @package     Axis_ShippingPickup
 * @subpackage  Axis_ShippingPickup_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingPickup
 * @subpackage  Axis_ShippingPickup_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingPickup_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    /**
     * $_code determines the internal 'code' name used to designate "this" payment module
     *
     * @var string
     */
    protected $_code = 'Pickup_Standard';

    /**
     * $_title is the displayed name for this payment method
     *
     * @var string
     */
    protected $_title = 'Pickup';

    /**
     * Obtain quote from shipping system/calculations
     *
     * @param array $request
     * @return array
     */
    public function getAllowedTypes($request)
    {
        $this->_types = array(
            array(
                'id' => $this->_code,
                'title' => $this->getTitle(),
                'price' => is_numeric($this->_config->price) ? $this->_config->price : 0
            )
        );

        return $this->_types;
    }
}