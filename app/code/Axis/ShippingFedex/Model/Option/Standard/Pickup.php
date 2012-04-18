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
 * @copyright   Copyright 2008-2012 Axis
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
class Axis_ShippingFedex_Model_Option_Standard_Pickup extends Axis_Config_Option_Array_Abstract
{
    const REGULAR_PICKUP          = 'REGULARPICKUP';
    const REQUEST_COURIER         = 'REQUESTCOURIER';
    const DROP_BOX                = 'DROPBOX';
    const BUSINESS_SERVICE_CENTER = 'BUSINESSSERVICECENTER';
    const STATION                 = 'STATION';
    
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::REGULAR_PICKUP          => 'Regular Pickup',
            self::REQUEST_COURIER         => 'Request Courier',
            self::DROP_BOX                => 'Drop Box',
            self::BUSINESS_SERVICE_CENTER => 'Business Service Center',
            self::STATION                 => 'Station'
        );
    }
}