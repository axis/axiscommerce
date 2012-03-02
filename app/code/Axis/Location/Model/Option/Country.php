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
 * @package     Axis_Location
 * @subpackage  Axis_Location_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Axis_Location_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Model_Option_Country implements Axis_Config_Option_Array_Interface
{
    protected static $_collection = null;
    
    /**
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        if (null === self::$_collection) {
            self::$_collection = Axis::single('location/country')
                ->select(array('id', 'name'))
                ->fetchPairs();
        }
        return self::$_collection;
    }
    
    /**
     *
     * @static
     * @param int $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        self::getConfigOptionsArray();
        $return = array();

        foreach(explode(Axis_Config::MULTI_SEPARATOR, $key) as $key) {
            if (array_key_exists($key, self::$_collection)) {
                $return[$key] = isset(self::$_collection[$key]) ?
                        self::$_collection[$key] : '';
            }
        }
        if (count($return) === count(self::$_collection)) {
            return 'All';
        }
        return implode(", ", $return);
    }
}
