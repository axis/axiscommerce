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
 * @package     Axis_Core
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Object implements ArrayAccess
{
    /**
     *
     * @var array
     */
    protected $_data = array();

    /**
     *  
     * @param array $data
     */
    public function  __construct($data = null)
    {
        if (is_array($data)) {
            $this->setFromArray($data);
        }
    }

    /**
     *
     * @param string $key
     * @return string
     */
    protected function _prepareKey($key)
    {
        return strtolower(preg_replace(
            array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $key
        ));
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (null === $key) {
            return $this->_data;
        }
        $key = $this->_prepareKey($key);

        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return Axis_Object Fluent interface
     */
    public function setData($key, $value)
    {
        if (empty($value)) {
            return $this;
        }
        //why?????????
        if ($value instanceof Axis_Object && !count($value->toArray())) {
            return $this;
        }
        //?????????
        $key = $this->_prepareKey($key);
//        if (is_array($value)) {
//            $value = new self($value);
//        }
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function &__get($key)
    {
//        return $this->getData($name);

        $key = $this->_prepareKey($key);

        $return = null;
        if (!isset($this->_data[$key])) {
            return $return;
        }

        if (/*is_scalar*/!is_object($this->_data[$key]))
        {
            $return = $this->_data[$key];
        } else {
            $return = &$this->_data[$key];
        }

        return $return;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return Axis_Object Fluent interface
     */
    public function __set($name, $value)
    {
        return $this->setData($name, $value);
    }

    /**
     *
     * @param  string  $key   The column key.
     * @return boolean
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * Unset row field value
     *
     * @param  string $key
     * @return Axis_Object Provides a fluent interface
     * @throws Axis_Exception
     */
    public function __unset($key)
    {
        if (!array_key_exists($key, $this->_data)) {
            throw new Axis_Exception("Specified property \"$key\" is not in the object");
        }
        
        unset($this->_data[$key]);
        return $this;
    }

    /**
     * Sets all data from an array.
     *
     * @param  array $data
     * @return Axis_Object Provides a fluent interface
     */
    public function setFromArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (empty($value) ||
                $value instanceof Axis_Object && !count($value->toArray())) {
                
                continue;
            }
//            $this->setData($key, $value);
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Convert object to array
     *
     * @param  array $attributes array of required attributes
     * @return array
     */
    public function __toArray(array $attributes = array())
    {
        if (empty($attributes)) {
            return $this->_data;
        }

        $result = array();
        foreach ($attributes as $attribute) {
            if (isset($this->_data[$attribute])) {
                $result[$attribute] = $this->_data[$attribute];
            } else {
                $result[$attribute] = null;
            }
        }
        return $result;
    }

    /**
     * Public wrapper for __toArray
     *
     * @param array $attributes
     * @return array
     */
    public function toArray(array $attributes = array())
    {
        return $this->__toArray($attributes);
    }

    /**
     *
     * @param string $name
     * @param mixed $argunents
     * @return mixed
     */
    public function __call($name, $argunents)
    {
        switch (substr($name, 0, 3)) {
            case 'get':
                return $this->getData(substr($name, 3));
                break;
            case 'set':
                if (!count($argunents)) {
                    $argunents[] = null;
                }
                return $this->setData(substr($name, 3), $argunents[0]);
                break;
            case 'has':
                return (bool) $this->getData(substr($name, 3));
                break;
        }
        throw new Axis_Exception(Axis::translate('core')->__(
            "Call to undefined method '%s'", get_class($this) . '::' . $name
        ));
    }

   /**
     * Proxy to __isset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Proxy to __get
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return string
     */
     public function offsetGet($offset)
     {
         return $this->__get($offset);
     }

     /**
      * Proxy to __set
      * Required by the ArrayAccess implementation
      *
      * @param string $offset
      * @param mixed $value
      */
     public function offsetSet($offset, $value)
     {
         $this->__set($offset, $value);
     }

     /**
      * Proxy to __unset
      * Required by the ArrayAccess implementation
      *
      * @param string $offset
      */
     public function offsetUnset($offset)
     {
         return $this->__unset($offset);
     }
}