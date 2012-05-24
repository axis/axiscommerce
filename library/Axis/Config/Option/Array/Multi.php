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
 * @copyright   Copyright 2008-2012 Axis
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
     * @param  mixed $value array or string
     * @return string
     */
    public function encode($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        return implode(self::SEPARATOR, $value);
    }

    /**
     *
     * @param  string $value
     * @return array
     */
    public function decode($value)
    {
        $value = str_replace(' ', '', $value);
        if (empty($value)) {
            return array();
        }
        return explode(self::SEPARATOR, $value);
    }
}
