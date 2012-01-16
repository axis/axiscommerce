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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Area
{
    const FRONT   = 'front'; // also test
    const ADMIN   = 'admin';
    const INSTALL = 'install';

    /**
     *
     * @var string
     */
    protected static $_area;

    /**
     *
     * @return string
     */
    public static function getArea()
    {
        return self::$_area;
    }

    /**
     *
     * @return void
     */
    public static function frontend()
    {
        self::$_area = self::FRONT;
    }

    /**
     *
     * @return bool
     */
    public static function isFrontend()
    {
        return self::FRONT === self::$_area;
    }

    /**
     *
     * @return void
     */
    public static function backend()
    {
        self::$_area = self::ADMIN;
    }

    /**
     *
     * @return bool
     */
    public static function isBackend()
    {
        return self::ADMIN === self::$_area;
    }

    /**
     *
     * @return void
     */
    public static function installer()
    {
        self::$_area = self::INSTALL;
    }

    /**
     *
     * @return bool
     */
    public static function isInstaller()
    {
        return self::INSTALL === self::$_area;
    }
}