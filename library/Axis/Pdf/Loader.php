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
 * @package     Axis_Pdf
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Pdf
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Pdf_Loader extends Zend_Loader
{
    /**
     *
     * @static
     * @param string $class
     * @param string $dirs [optional]
     */
    public static function loadClass($class, $dirs = null)
    {
        $filename = strtolower($class) . ".cls.php";
        require_once(DOMPDF_INC_DIR . "/$filename");
        parent::loadClass($class);
    }

    /**
     *
     * @static
     * @param string $class
     * @return <type>
     */
    public static function autoload($class)
    {
        try {
            self::loadClass($class);
            return $class;
        } catch (Axis_Exception $e) {
            return false;
        }
    }
}