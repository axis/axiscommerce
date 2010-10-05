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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentPaypal_StandardController extends Axis_Core_Controller_Front_Secure
{
    
    /**
     * customer cancel payment from paypal
     */
    public function cancelAction()
    {
        if (!$customerId = Axis::getCustomerId()) {
            return;
        }
        $orderId = $this->_getParam('invoice');
        if (!$orderId) {
            return;
        }
        $order = Axis::single('sales/order')->find($orderId)->current();
        if (!$order instanceof Axis_Sales_Model_Order_Row) {
            return;
        }
        if ($customerId != $order->customer_id) {
            return;
        }
        $order->setStatus('cancel', 'Canceled on paypal');
        $this->_redirect('/checkout/cancel');
    }
    
    /**
     * when paypal returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN. 
     */
    public function successAction()
    {
        // @todo create for this module table with addational information of transaction
        // and on admin need render payer_email in order view
        $this->_redirect('/checkout/success');
    }
    
    /**
     * when paypal returns via ipn
     * cannot have any output here
     * validate IPN data
     * if data is valid need to update the database that the user has
     */
    public function ipnAction() 
    {
        Axis::single('PaymentPaypal/Standard')->ipnSubmit($this->_getAllParams());    
    }
}