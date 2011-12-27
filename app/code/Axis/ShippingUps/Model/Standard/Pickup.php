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
 * @package     Axis_ShippingUps
 * @subpackage  Axis_ShippingUps_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingUps
 * @subpackage  Axis_ShippingUps_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_ShippingUps_Model_Standard_Pickup implements Axis_Config_Option_Array_Interface
{
    const RDP = '01';
    const CC  = '03';
    const OTP = '06';
    const OCA = '07';
    const LC  = '19';
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::RDP => 'Daily Pickup',
            self::CC  => 'Customer Counter',
            self::OTP => 'One Time Pickup',
            self::OCA => 'On Call Air',
            self::LC  => 'Letter Center'
        );
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getConfigOptionName($id)
    {
        $options = self::getConfigOptionsArray();
        return isset($options[$id]) ? $options[$id] : '';
    }
}