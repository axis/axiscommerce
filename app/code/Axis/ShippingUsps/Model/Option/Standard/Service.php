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
 * @copyright   Copyright 2008-2012 Axis
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
class Axis_ShippingUsps_Model_Option_Standard_Service extends Axis_Config_Option_Array_Multi
{
    const ALL         = 'ALL';
    
    const FIRST_CLASS = 'FIRST CLASS';
    const PRIORITY    = 'PRIORITY';
    const EXPRESS     = 'EXPRESS';
    const BPM         = 'BPM';
    const PARCEL      = 'PARCEL';
    const MEDIA       = 'MEDIA';
    const LIBRARY     = 'LIBRARY';
    
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::FIRST_CLASS => 'First-Class',
            self::PRIORITY    => 'Priority Mail',
            self::EXPRESS     => 'Express Mail',
            self::BPM         => 'Bound Printed Matter',
            self::PARCEL      => 'Parcel Post',
            self::MEDIA       => 'Media Mail',
            self::LIBRARY     => 'Library'
        );
    }
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getDeafult()
    {
        return self::ALL;
    }
}