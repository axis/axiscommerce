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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Option_Order_Status_Text implements Axis_Config_Option_Array_Interface
{  
    /**
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        return Axis::single('sales/order_status_text')
                ->select(array('status_id', 'status_name'))
                ->where('language_id = ?', Axis_Locale::getLanguageId())
                ->fetchPairs();
    }

    /**
     *
     * @static
     * @param string $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        return Axis::single('sales/order_status_text')
            ->select('status_name')
            ->where('status_id = ?', $key)
            ->where('language_id = ?', Axis_Locale::getLanguageId())
            ->fetchOne();
    }
}