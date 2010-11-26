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
 * @package     Axis_Filter
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Filter
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Filter_DbField implements Zend_Filter_Interface
{
    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @static
     * @var mixed
     */
    protected static $_unicodeEnabled;

    /**
     * Sets default option values for this instance
     *
     * @param  boolean $allowWhiteSpace
     * @return void
     */
    public function __construct($allowWhiteSpace = false)
    {
        if (null === self::$_unicodeEnabled) {
            self::$_unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
        }
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, removing all but "alphabetic & _" characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!self::$_unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^a-zA-Z0-9\_]/';
        } else {
            $pattern = '/[^\p{L}\_0-9]/u';
        }

        return preg_replace($pattern, '', (string) $value);
    }
}
