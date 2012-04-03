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
abstract class Axis_Config_Option_Array_Abstract implements 
    IteratorAggregate, Countable, ArrayAccess,
//    Axis_Config_Option_Array_Interface, 
    Axis_Config_Option_Encodable_Interface
    
{
    const MULTI_SEPARATOR = ',';
    
    /**
     *
     * @var array 
     */
    protected $_collection = array();
    
    public function __construct() 
    {
        $this->_collection = $this->_loadCollection();
    }
    
    abstract protected function _loadCollection();
    
    /**
     * Implementation of IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator 
     */    
    public function getIterator()
    {
        return new ArrayIterator($this->_collection);
    }
    
    /**
     * Retireve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        return count($this->_collection);
    }
    
    /**
     * Required by the ArrayAccess implementation
     *
     * @param mixed $offset
     * @param mixed $value 
     */
    public function offsetSet($offset, $value) 
    {
        throw new Axis_Exception('Option c\'ant be added');
//        if (is_null($offset)) {
//            $this->_collection[] = $value;
//        } else {
//            $this->_collection[$offset] = $value;
//        }
    }
    
    /**
     *
     * @param mixed $offset
     * @return bool 
     */
    public function offsetExists($offset) 
    {
        return isset($this->_collection[$offset]);
//        
//        foreach($this->decode($offset) as $key) {
//            if (!array_key_exists($key, $this->_collection)) {
//                return false;
//            }
//        }
//        return true;
    }
    
    /**
     * Required by the ArrayAccess implementation
     * 
     * @param mixed $offset 
     */
    public function offsetUnset($offset) 
    {
        throw new Axis_Exception('Option c\'ant be remove');
//        unset($this->_collection[$offset]);
    }
    
    /**
     * Required by the ArrayAccess implementation
     * 
     * @param mixed $offset
     * @return mixed 
     */
    public function offsetGet($offset) 
    {
        return isset($this->_collection[$offset]) ? $this->_collection[$offset] : null;
        
//        $return = array();
//        foreach($this->decode($offset) as $key) {
//            if (array_key_exists($key, $this->_collection)) {
//                $return[$key] = $this->_collection[$key];
//            }
//        }
////        if (count($return) === count($this->_collection)) {
////            return 'All';
////        }
//        return $this->encode($return);
    }
    
    /**
     *
     * @return array
     */
    public function toArray() 
    {
        return $this->_collection;
    }
    
    /**
     *
     * @param  array $value
     * @return string
     */
    public function encode($value)
    {
        if (!is_array($value)) {
            throw new Axis_Exception('get me array');
        }
        //@todo remove $value !in_array($this->_collection)
        return implode(self::MULTI_SEPARATOR, $value);
    }
    
    /**
     *
     * @param  string $value
     * @return array
     */
    public function decode($value)
    {
        return explode(self::MULTI_SEPARATOR, $value);
    }
}