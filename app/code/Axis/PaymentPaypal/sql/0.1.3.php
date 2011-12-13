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
 * @package     Axis_PaymentPaypal
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentPaypal_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = '';

    public function up()
    {
        //rename 'transactionMode' => 'paymentAction'
        $row = Axis::single('core/config_field')->select()
            ->where('path = ?', 'payment/Paypal_Express/transactionMode')
            ->fetchRow();
        $row->path = 'payment/Paypal_Express/paymentAction';
        $row->save();
        
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'payment/Paypal_Express/transactionMode')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->path = 'payment/Paypal_Express/paymentAction';
            $row->save();
        }
        //remove 'payment/Paypal_Standard/type'
        Axis::single('core/config_field')->select()
            ->where('path = ?', 'payment/Paypal_Standard/type')
            ->fetchRow()
            ->delete()
            ;
        //payment/Paypal_Standard/isDebugMode
        Axis::single('core/config_field')->select()
            ->where('path = ?', 'payment/Paypal_Standard/isDebugMode')
            ->fetchRow()
            ->delete()
            ;
        
        //remove 'payment/Paypal_Direct/debugging'
        Axis::single('core/config_field')->select()
            ->where('path = ?', 'payment/Paypal_Direct/debugging')
            ->fetchRow()
            ->delete()
            ;

        $paths = array(
            'payment/Paypal_Standard/transactionType' => 'Axis_PaymentPaypal_Model_Standard_TransactionType',
            
            'payment/Paypal_Direct/server'            => 'Axis_PaymentPaypal_Model_Api_ServerType',
            'payment/Paypal_Express/server'           => 'Axis_PaymentPaypal_Model_Api_ServerType',
            
            'payment/Paypal_Direct/mode'              => 'Axis_PaymentPaypal_Model_Api_Type',
            'payment/Paypal_Express/mode'             => 'Axis_PaymentPaypal_Model_Api_Type',
            
            'payment/Paypal_Standard/orderStatusId'   => 'Axis_Sales_Model_Order_Status',
            'payment/Paypal_Express/orderStatusId'    => 'Axis_Sales_Model_Order_Status',
            
            'payment/Paypal_Direct/paymentAction'     => 'Axis_PaymentPaypal_Model_Api_PaymentAction',
            'payment/Paypal_Standard/paymentAction'   => 'Axis_PaymentPaypal_Model_Api_PaymentAction',
            'payment/Paypal_Express/paymentAction'    => 'Axis_PaymentPaypal_Model_Express_PaymentAction'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->config_options = null; 
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}