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
 * @package     Axis_PaymentCreditCard
 * @subpackage  Axis_PaymentCreditCard_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * Manual Credit Card payment method
 * This module is used for MANUAL processing of credit card data collected from customers.
 * It should ONLY be used if no other gateway is suitable, AND you must have SSL active on your server for your own protection.
 *
 * @category    Axis
 * @package     Axis_PaymentCreditCard
 * @subpackage  Axis_PaymentCreditCard_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentCreditCard_Model_Standard extends Axis_Method_Payment_Model_Card_Abstract
{
    protected $_code  = 'CreditCard_Standard';
    protected $_title = 'Credit Card';

    public function postProcess(Axis_Sales_Model_Order_Row $order)
    {
        $this->saveCreditCard($order);
    }
}
