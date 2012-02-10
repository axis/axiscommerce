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
 * @subpackage  Axis_Config_Handler
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Config
 * @subpackage  Axis_Config_Handler
 * @author      Axis Core Team <core@axiscommerce.com>
 */
interface Axis_Config_Handler_Interface extends Axis_Config_Option_Interface
{
    /**
     *
     * @static
     * @param  array $params
     * @return string
     */
    public static function prepareConfigOptionValue($params);

    /**
     * @todo move to spec helpers
     * Get html
     *
     * @static
     * @param string $value
     * @return string
     */
    public static function getHtml($value, Zend_View_Interface $view = null);

    /**
     * Get config
     * 
     * from db format to current value 
     *
     * @static
     * @param  string $value
     * @return mixed
     */
    public static function getConfig($value);
}
