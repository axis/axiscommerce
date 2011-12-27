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
class Axis_Sales_Model_Order_Status_Text extends Axis_Db_Table implements Axis_Config_Option_Array_Interface
{
    protected $_name = "sales_order_status_text";
    protected $_primary = array('status_id', 'language_id');
        
    public function getList()
    {
        return $this->fetchAll("language_id = " . (int) Axis_Locale::getLanguageId())->toArray();
    }
    
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
     * @param string $id
     * @return string
     */
    public static function getConfigOptionName($id)
    {
        return Axis::single('sales/order_status_text')
            ->select('status_name')
            ->where('status_id = ?', $id)
            ->where('language_id = ?', Axis_Locale::getLanguageId())
            ->fetchOne();
    }
}