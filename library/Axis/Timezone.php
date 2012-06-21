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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Locale
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Timezone
{
    const DEFAULT_TIMEZONE  = 'America/Los_Angeles';

    /**
     *
     * @return string
     */
    public static function getTimezone()
    {
        return @date_default_timezone_get();
    }

    /**
     *
     * @param string $timezone
     * @return boolean
     * @throws Axis_Exception
     */
    public static function setTimezone($timezone)
    {
        if (function_exists('timezone_open') && !@timezone_open($timezone)) {
            throw new Axis_Exception("timezone ($timezone) is not a known timezone");
        }

        if (!@date_default_timezone_set($timezone)) {
            @date_default_timezone_set(self::DEFAULT_TIMEZONE);
            throw new Axis_Exception("timezone ($timezone) is not set correct");
        }
        return true;
    }
}
