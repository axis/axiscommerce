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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Option_Shipping extends Axis_Config_Option_Array_Multi
{
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        $methods = array();
        foreach (Axis_Shipping::getMethods() as $methodCode => $method) {
            $methods[$methodCode] = $method->getTitle();
        }
        return $methods;
    }
}