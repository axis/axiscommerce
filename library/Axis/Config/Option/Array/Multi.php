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
 * @package     Axis_Config
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Config
 * @author      Axis Core Team <core@axiscommerce.com>
 */
abstract class Axis_Config_Option_Array_Multi extends Axis_Config_Option_Array_Abstract implements Axis_Config_Option_Encodable_Interface
{
    const SEPARATOR = ',';
    
    /**
     *
     * @param  array $value
     * @return string
     */
    public function encode($value)
    {
        //@todo remove $value !in_array($this->_collection)
        return implode(self::SEPARATOR, (array)$value);
    }
    
    /**
     *
     * @param  string $value
     * @return array
     */
    public function decode($value)
    {
        return explode(self::SEPARATOR, $value);
    }
    
//    /**
//     *
//     * @param mixed $offset
//     * @return bool 
//     */
//    public function offsetExists($offset) 
//    {
//        return isset($this->_collection[$offset]);
////        
////        foreach($this->decode($offset) as $key) {
////            if (!array_key_exists($key, $this->_collection)) {
////                return false;
////            }
////        }
////        return true;
//    }
//        
//    /**
//     * Required by the ArrayAccess implementation
//     * 
//     * @param mixed $offset
//     * @return mixed 
//     */
//    public function offsetGet($offset) 
//    {
//        return isset($this->_collection[$offset]) ? $this->_collection[$offset] : null;
//        
////        $return = array();
////        foreach($this->decode($offset) as $key) {
////            if (array_key_exists($key, $this->_collection)) {
////                $return[$key] = $this->_collection[$key];
////            }
////        }
//////        if (count($return) === count($this->_collection)) {
//////            return 'All';
//////        }
////        return $this->encode($return);
//    }
}
