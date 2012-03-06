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
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_Sitemap_Model_Option_Frequency implements Axis_Config_Option_Array_Interface
{   
    const ALWAYS  = 'always';
    const HOURLY  = 'hourly';
    const DAILY   = 'daily';
    const WEEKLY  = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY  = 'yearly';
    const NEVER   = 'never';
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::ALWAYS  => ucfirst(self::ALWAYS),
            self::HOURLY  => ucfirst(self::HOURLY),
            self::DAILY   => ucfirst(self::DAILY),
            self::WEEKLY  => ucfirst(self::WEEKLY),
            self::MONTHLY => ucfirst(self::MONTHLY),
            self::YEARLY  => ucfirst(self::YEARLY),
            self::NEVER   => ucfirst(self::NEVER)
        );
    }

    /**
     *
     * @static
     * @param string $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        $options = self::getConfigOptionsArray();
        return isset($options[$key]) ? $options[$key] : '';
    }
}