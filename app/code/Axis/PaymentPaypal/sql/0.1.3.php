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
        if ($row) {
            $row->path = 'payment/Paypal_Express/paymentAction';
            $row->save();
        }
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'payment/Paypal_Express/transactionMode')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->path = 'payment/Paypal_Express/paymentAction';
            $row->save();
        }
        //remove old
        $paths = array(
            'payment/Paypal_Standard/type',
            'payment/Paypal_Standard/isDebugMode',
            'payment/Paypal_Direct/debugging'
            
        );
        $rowset = Axis::single('core/config_field')->select()
            ->where('path IN (?)', $paths)
            ->fetchRowset()
            ;
        
        foreach ($rowset as $row) {
            $row->delete();
        }

        $paths = array(
            'payment/Paypal_Standard/transactionType' => 'paymentPaypal/option_standard_transactionType',
            
            'payment/Paypal_Direct/server'            => 'paymentPaypal/option_serverType',
            'payment/Paypal_Express/server'           => 'paymentPaypal/option_serverType',
            
            'payment/Paypal_Direct/mode'              => 'paymentPaypal/option_type',
            'payment/Paypal_Express/mode'             => 'paymentPaypal/option_type',
            
            'payment/Paypal_Standard/orderStatusId'   => 'sales/option_order_status',
            'payment/Paypal_Express/orderStatusId'    => 'sales/option_order_status',
            
            'payment/Paypal_Direct/paymentAction'     => 'paymentPaypal/option_paymentAction',
            'payment/Paypal_Standard/paymentAction'   => 'paymentPaypal/option_paymentAction',
            'payment/Paypal_Express/paymentAction'    => 'paymentPaypal/option_express_paymentAction'
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}