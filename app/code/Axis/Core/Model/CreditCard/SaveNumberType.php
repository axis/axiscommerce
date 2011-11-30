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
class Axis_Core_Model_CreditCard_SaveNumberType implements Axis_Collect_Interface
{
    /**
     *
     * @var const array
     */
    static protected $_actions = array(
        'dont_save'         => 'Don\'t save',
        'last_four'         => 'Last 4 digits',
        'first_last_four'   => 'First and last 4 digits',
        'partial_email'     => 'First and last 4 digits to database, rest send by email',
        'complete'          => 'Save complete number',
    );

    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        return self::$_actions;
    }

    /**
     *
     * @static
     * @param string $id
     * @return mixed string|void
     */
    public static function getName($id)
    {
        if (!$id || !isset(self::$_actions[$id])) {
            return '';
        }
        return self::$_actions[$id];
    }
}