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
 * @package     Axis_Collect
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Collect_Language implements Axis_Collect_Interface
{
    protected static $_collection = null;

    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        if (null === self::$_collection) {
            self::$_collection = Axis::single('locale/language')
                ->select(array('id', 'language'))
                ->fetchPairs();
        }
        return self::$_collection;
    }

    /**
     *
     * @static
     * @param int $id
     * @return string
     */
    public static function getName($id)
    {
        return Axis::single('locale/language')->getLanguageById($id);
    }
}