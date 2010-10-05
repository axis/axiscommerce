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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Address extends Axis_Object
{
    protected $_fields = array(
        'id',
        'customer_id',
        'gender',
        'company',
        'phone',
        'fax',
        'email',
        'ip',
        'firstname',
        'lastname',
        'street_address',
        'suburb',
        'city',
        'zone',
        'postcode',
        'country',
        'address_format_id',
        'tax_id',
        'password',
        'password_confirm',
        'register_password',
        'default_shipping',
        'default_billing'
    );

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return Axis_Address
     */
    public function setData($name, $value)
    {
        if (empty($value)) {
            return $this;
        }
        if ($value instanceof Axis_Object && !count($value->toArray())) {
            return $this;
        }
        $name = strtolower(preg_replace(
            array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $name
        ));
        if (!in_array($name, $this->_fields)) {
            return $this;
        }
        if (in_array($name, array('country', 'zone'))) {
            if (is_array($value)) {
                $value = new Axis_Object($value);
            }
            if (!$value instanceof Axis_Object) {
                return $this;
            }
        }
        $this->_data[$name] = $value;
        return $this;

    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function getData($name = null)
    {
        if (null === $name) {
            return $this->_data;
        }
        $name = strtolower(preg_replace(
            array('/(.)([A-Z])/', '/(.)(\d+)/'), "$1_$2", $name
        ));
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
    }

    /**
     * Sets all data from an array.
     *
     * @param  array $data
     * @return Axis_Object Provides a fluent interface
     */
    public function setFromArray(array $data)
    {
        foreach ($data as $name => $value) {
            if (!in_array($name, $this->_fields)) {
                continue;
            }
            if (in_array($name, array('country', 'zone'))) {
                if (is_array($value)) {
                    $value = new Axis_Object($value);
                }
                if (!$value instanceof Axis_Object) {
                    continue;
                }
            }
            $this->_data[$name] = $value;
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
        $result = array();
        if (empty($attributes)) {
            $result = $this->_data;
        } else {
            foreach ($attributes as $attribute) {
                if (isset($this->_data[$attribute])) {
                    $result[$attribute] = $this->_data[$attribute];
                } else {
                    $result[$attribute] = null;
                }
            }
        }
        if (isset($result['country'])) {
            $country = $result['country'];
            $result['country'] = $country->toArray();
        }
        if (isset($result['zone'])) {
            $country = $result['zone'];
            $result['zone'] = $country->toArray();
        }
        return $result;
    }

    public function toFlatArray(array $attributes = array())
    {
        $rowset = $this->toArray($attributes);

        foreach ($rowset as $key => $value) {
            if (!in_array($key, array('country', 'zone'))) {
                continue;
            }
            foreach ($value as $subkey => $subvalue) {
                $rowset[$key . '_' . $subkey] = $subvalue;
            }
            unset($rowset[$key]);
        }
        return $rowset;
    }
}