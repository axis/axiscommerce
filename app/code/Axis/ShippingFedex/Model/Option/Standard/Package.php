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
 * @package     Axis_ShippingFedex
 * @subpackage  Axis_ShippingFedex_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingFedex
 * @subpackage  Axis_ShippingFedex_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_ShippingFedex_Model_Option_Standard_Package implements Axis_Config_Option_Array_Interface
{
    const FEDEX_ENVELOPE = 'FEDEXENVELOPE';
    const FEDEX_PAK      = 'FEDEXPAK';
    const FEDEX_BOX      = 'FEDEXBOX';
    const FEDEX_TUBE     = 'FEDEXTUBE';
    const FEDEX_10KG_BOX = 'FEDEX10KGBOX';
    const FEDEX_25KG_BOX = 'FEDEX25KGBOX';
    const YOUR_PACKAGING = 'YOURPACKAGING';
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::FEDEX_ENVELOPE => 'FedEx Envelope',
            self::FEDEX_PAK      => 'FedEx Pak',
            self::FEDEX_BOX      => 'FedEx Box',
            self::FEDEX_TUBE     => 'FedEx Tube',
            self::FEDEX_10KG_BOX => 'FedEx 10kg Box',
            self::FEDEX_25KG_BOX => 'FedEx 25kg Box',
            self::YOUR_PACKAGING => 'Your Packaging'
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