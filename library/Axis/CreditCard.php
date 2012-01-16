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
 * @package     Axis_Payment
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Payment
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_CreditCard extends Axis_Object
{
    public function getData($name = null)
    {
        if (isset($this->_data[$name])) {
            $crypt = Axis_Crypt::factory();
            return $crypt->decrypt($this->_data[$name]);
        }
        return null;
    }

    public function setData($name, $value)
    {
        $crypt = Axis_Crypt::factory();
        $value = empty($value) ? null : $crypt->encrypt($value);
        $this->_data[$name] = $value;
        return $this;
    }
}