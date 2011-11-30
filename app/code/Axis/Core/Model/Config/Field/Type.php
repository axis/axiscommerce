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
class Axis_Core_Model_Config_Field_Type implements Axis_Collect_Interface
{
    /**
     * @static
     * @var const array
     */
    static protected $_types = array(
        'radio'    => 'bool',
        'checkbox' => 'multiple',
        'select'   => 'select',
        'string'   => 'string',
        //'handler' => 'handler',
        'textarea'     => 'text'
    );

    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        return self::$_types;
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getName($id)
    {
        return self::$_types[$id];
    }
}