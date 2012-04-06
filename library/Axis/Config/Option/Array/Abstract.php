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
abstract class Axis_Config_Option_Array_Abstract implements IteratorAggregate, Countable, ArrayAccess
//    Axis_Config_Option_Array_Interface, 
//    Axis_Config_Option_Encodable_Interface
    
{
    /**
     *
     * @var array 
     */
    protected $_collection = array();
    
    /**
     *
     * @var bool 
     */
    protected $_isLoaded = false;

    abstract protected function _loadCollection();
       
    protected function _load() 
    {
        if (!$this->_isLoaded) {
            $this->_collection = $this->_loadCollection();
            $this->_isLoaded = true;
        }
    }

    /**
     * Implementation of IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator 
     */    
    public function getIterator()
    {
        $this->_load();
        return new ArrayIterator($this->_collection);
    }
    
    /**
     * Retireve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->_load();
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
        $this->_load();
        return isset($this->_collection[$offset]);
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
        $this->_load();
        return isset($this->_collection[$offset]) ? $this->_collection[$offset] : null;
    }
    
    /**
     *
     * @return array
     */
    public function toArray() 
    {
        $this->_load();
        return $this->_collection;
    }
}