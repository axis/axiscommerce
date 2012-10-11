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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sales_Upgrade_0_2_5 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.5';

    protected $_info = '';

    public function up()
    {
        $models = array(
            'OrderStatus'                 => 'sales/option_order_status',
            'OrderStatusText'             => 'sales/option_order_status_text',
            'CreditCard'                  => 'sales/option_order_creditCard_type',
            'CreditCard_SaveNumberAction' => 'sales/option_order_creditCard_saveNumberType'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($models[$row->model])) {
                $row->model = $models[$row->model];
                $row->save();
            }
        }
        
        $row = Axis::single('core/config_field')->select()
            ->where('path = ?', 'sales/order/defaultStatusId')
            ->fetchRow();
        $row->model = 'sales/option_order_status';
        $row->save();
    }
}
