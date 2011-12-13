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
class Axis_ShippingUps_Model_Standard_Package implements Axis_Config_Option_Interface
{
    const CP   = '00';
    const ULE  = '01';
    const UT   = '03';
    const UEB  = '21';
    const UW25 = '24';
    const UW10 = '25';
    const PLT  = '30';
    const SEB  = '2a';
    const MEB  = '2b';
    const LEB  = '2c';
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::CP   => 'Customer Packaging',
            self::ULE  => 'UPS Letter Envelope',
            self::UT   => 'UPS Tube',
            self::UEB  => 'UPS Express Box',
            self::UW25 => 'UPS Worldwide 25 kilo',
            self::UW10 => 'UPS Worldwide 10 kilo',
            self::PLT  => 'Pallet',
            self::SEB  => 'Small Express Box',
            self::MEB  => 'Medium Express Box',
            self::LEB  => 'Large Express Box'
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