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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Validate
 * @author      Axis Core Team <core@axiscommerce.com>
 */
final class Axis_Validate
{
    /**
     *
     * @var array
     */
    private $_validators = array();

    /**
     *
     * @var array
     */
    private $_messages = array();

    /**
     *
     * @param array $validators
     */
    public function __construct(array $validators = array())
    {
        $this->_validators = $validators;
    }

    /**
     *
     * @param array $validators
     */
    public function setValidators(array $validators = array())
    {
        $this->_validators = $validators;
    }

    /**
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->_validators;
    }

    /**
     *
     * @param  array $values
     * @return bool
     */
    public function validate($values)
    {
        $this->_messages = array();
        foreach ($this->_validators as $fieldName => &$validator) {
            if (!$validator->isValid($values[$fieldName])) {
                $this->_messages[$fieldName] = $validator->getMessages();
            }
        }
        if (sizeof($this->_messages)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}