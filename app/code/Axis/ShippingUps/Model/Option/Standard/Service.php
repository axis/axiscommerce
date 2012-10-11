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
 * @copyright   Copyright 2008-2012 Axis
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
class Axis_ShippingUps_Model_Option_Standard_Service extends Axis_Config_Option_Array_Multi
{
    /**
     *
     * @var array
     */
    private static $_services = array(
        
        '1DM'    => 'Next Day Air Early AM',
        '1DML'   => 'Next Day Air Early AM Letter',
        '1DA'    => 'Next Day Air',
        '1DAL'   => 'Next Day Air Letter',
        '1DAPI'  => 'Next Day Air Intra (Puerto Rico)',
        '1DP'    => 'Next Day Air Saver',
        '1DPL'   => 'Next Day Air Saver Letter',
        '2DM'    => '2nd Day Air AM',
        '2DML'   => '2nd Day Air AM Letter',
        '2DA'    => '2nd Day Air',
        '2DAL'   => '2nd Day Air Letter',
        '3DS'    => '3 Day Select',
        'GND'    => 'Ground',
        'GNDCOM' => 'Ground Commercial',
        'GNDRES' => 'Ground Residential',
        'STD'    => 'Canada Standard',
        'XPR'    => 'Worldwide Express',
        'WXS'    => 'Worldwide Express Saver',
        'XPRL'   => 'Worldwide Express Letter',
        'XDM'    => 'Worldwide Express Plus',
        'XDML'   => 'Worldwide Express Plus Letter',
        'XPD'    => 'Worldwide Expedited'
    );

    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return self::$_services;
    }
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getDeafult()
    {
        return implode(self::SEPARATOR, array_keys(self::$_services));
    }
}