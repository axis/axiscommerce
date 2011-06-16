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
 * @package     Axis_ShippingPerWeightUnit
 * @subpackage  Axis_ShippingPerWeightUnit_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * "Per Weight Unit" shipping module, allowing you to offer per-unit-rate shipping options
 *
 * @category    Axis
 * @package     Axis_ShippingPerWeightUnit
 * @subpackage  Axis_ShippingPerWeightUnit_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingPerWeightUnit_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    protected $_code        = 'PerWeightUnit_Standard';
    protected $_title       = 'Per Weight Unit Flat Rate';
    protected $_description = 'PerWeightUnit shipping method';
    protected $_icon        = '';

    /**
     * Obtain quote from shipping system/calculations
     *
     * @param array $request
     * @return array
     */
    public function getAllowedTypes($request)
    {
        $price      = is_numeric($this->_config->price) ? $this->_config->price : 0;
        $handling   = is_numeric($this->_config->handling) ? $this->_config->handling : 0;
        $this->_types = array(
            array(
                'id' => $this->_code,
                'title' => $this->getTitle(),
                'price' => $price * $request['weight'] + $handling
            )
        );
        return $this->_types;
    }
}