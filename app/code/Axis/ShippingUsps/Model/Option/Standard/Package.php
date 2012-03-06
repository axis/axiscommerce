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
 * @package     Axis_ShippingUsps
 * @subpackage  Axis_ShippingUsps_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingUsps
 * @subpackage  Axis_ShippingUsps_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_ShippingUsps_Model_Option_Standard_Package implements Axis_Config_Option_Array_Interface
{
    const VARIABLE           = 'VARIABLE';
    const FLAT_RATE_BOX      = 'FLAT RATE BOX';
    const FLAT_RATE_ENVELOPE = 'FLAT RATE ENVELOPE';
    const RECTANGULAR        = 'RECTANGULAR';
    const NONRECTANGULAR     = 'NONRECTANGULAR';
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::VARIABLE           => 'Variable',
            self::FLAT_RATE_BOX      => 'Flat-Rate Box',
            self::FLAT_RATE_ENVELOPE => 'Flat-Rate Envelope',
            self::RECTANGULAR        => 'Rectangular',
            self::NONRECTANGULAR     => 'Non-rectangular'
        );
    }

    /**
     *
     * @static
     * @param string $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        $options = self::getConfigOptionsArray();
        return isset($options[$key]) ? $options[$key] : '';
    }
}