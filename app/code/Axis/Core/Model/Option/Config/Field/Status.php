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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Option_Config_Field_Status implements Axis_Config_Option_Array_Interface
{
    /**
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        $translate = Axis::translate('Axis_Core');
        return array(
            'required' => $translate->__('Required'),
            'optional' => $translate->__('Optional'),
            'disabled' => $translate->__('Disabled')
        );
    }

    /**
     *
     * @static
     * @param int $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        $options = self::getConfigOptionsArray();
        if (isset($options[$key])) {
            return $options[$key];
        }
        return 'Undefined';
    }
}