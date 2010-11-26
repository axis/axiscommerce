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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Total extends Axis_Db_Table
{
    protected $_name = 'sales_order_total';
    
    public function getShipping($orderId)
    {
        return $this->select('value')
            ->where('order_id = ?', $orderId)
            ->where('code = \'shipping\'')
            ->fetchOne();
    }
    
    public function getTax($orderId)
    {
        return $this->select('value')
            ->where('order_id = ?', $orderId)
            ->where('code = \'tax\'')
            ->fetchOne();
    }
    
    public function getShippingTax($orderId) 
    {
        return $this->select('value')
            ->where('order_id = ?', $orderId)
            ->where('code = \'shipping_tax\'')
            ->fetchOne();
    }
    
    public function getSubtotal($orderId)
    {
        return $this->select('value')
            ->where('order_id = ?', $orderId)
            ->where('code = \'subtotal\'')
            ->fetchOne();
    }
}