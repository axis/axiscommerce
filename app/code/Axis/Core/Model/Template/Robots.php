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
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template_Robots implements Axis_Config_Option_Array_Interface
{
    //@todo delimeter ','
    const FOLLOW   = 'FOLLOW';  
    const NOFOLLOW = 'NOFOLLOW'; 
    const INDEX    = 'INDEX'; 
    const NOINDEX  = 'NOINDEX'; 
    // @todo PROFIT:
    // 0 - nofollow noindex, 1 - follow noindex, 2 - nofollow index, 3 - follow index
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionsArray()
    {
        return array(
            self::INDEX . ' ' . self::FOLLOW     => self::INDEX . ',' . self::FOLLOW,
            self::INDEX . ' ' . self::NOFOLLOW   => self::INDEX . ',' . self::NOFOLLOW,
            self::NOINDEX . ' ' . self::FOLLOW   => self::NOINDEX . ',' . self::FOLLOW,
            self::NOINDEX .  ' ' .self::NOFOLLOW => self::NOINDEX . ',' . self::NOFOLLOW
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
    
        
    /**
     *
     * @static
     * @return const array
     */
    public static function getConfigOptionDeafultValue()
    {
        return self::INDEX . ' ' . self::FOLLOW;
    }
}