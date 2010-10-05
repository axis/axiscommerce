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
 * @package     Axis_Validate
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Validate
 * @subpackage  Validator
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Validate_PasswordEqual extends Zend_Validate_Abstract
{
    /**
     *
     * @var string
     */
    private $_password;

    /**
     *
     * @param string $password
     */
    public function __construct($password)
    {
        $this->_password = $password;
    }

    /**
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (md5($value) != $this->_password) {
            $this->_messages[] = Axis::translate('core')->__(
                'Incorrect password'
            );
            return false;
        }
        return true;
    }
}