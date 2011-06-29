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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Validate
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Validate_PasswordConfirmation extends Zend_Validate_Abstract
{
    protected $_elementId = 'password';
    protected $_confirmElementId = 'password_confirm';

    /**
     *
     * @param string $elementId
     * @param string $comfirmId
     */
    public function  __construct(
        $elementId = 'password', $comfirmId = 'password_confirm') {

        $this->_elementId = $elementId;
        $this->_confirmElementId = $comfirmId;
    }

    /**
     *
     * @param string $value
     * @param array $context
     * @param string $confirmKey
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (is_array($context)) {
            if (isset($context[$this->_confirmElementId])
                && ($value == $context[$this->_confirmElementId]))
            {
                return true;
            }
        } elseif (is_string($context) && ($value == $context)) {
            return true;
        }

        $this->_messages[] = Axis::translate('core')->__(
            'Password confirmation does not match'
        );
        return false;
    }
}