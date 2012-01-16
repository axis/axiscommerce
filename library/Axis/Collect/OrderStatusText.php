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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Collect_OrderStatusText implements Axis_Collect_Interface
{
    /**
     * @static
     * @return array
     */
    public static function collect()
    {
        return Axis::single('sales/order_status_text')
                ->select(array('status_id', 'status_name'))
                ->where('language_id = ?', Axis_Locale::getLanguageId())
                ->fetchPairs();
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getName($id)
    {
        return Axis::single('sales/order_status_text')
            ->select('status_name')
            ->where('status_id = ?', $id)
            ->where('language_id = ?', Axis_Locale::getLanguageId())
            ->fetchOne();
    }
}