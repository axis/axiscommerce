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
class Axis_Sales_Model_Observer
{
    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function notifyAdminNewOrder(Axis_Sales_Model_Order_Row $order)
    {
        $mail = new Axis_Mail();
        $mail->setConfig(array(
            'event'   => 'order_new-owner',
            'subject' => Axis::translate('sales')->__('Order created'),
            'data'    => array('order' => $order),
            'to'      =>  Axis_Collect_MailBoxes::getName(
                Axis::config()->sales->order->email
            )
        ));
        return $mail->send();

    }

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function notifyCustomerNewOrder(Axis_Sales_Model_Order_Row $order)
    {
        $customer = Axis::single('account/customer')
            ->find($order->customer_id)
            ->current();

        $mail = new Axis_Mail();
        $mail->setConfig(array(
            'event'   => 'order_new-customer',
            'subject' => Axis::translate('sales')->__('Your create new order'),
            'data'    => array(
                'firstname' => $customer->firstname,
                'lastname'  => $customer->lastname,
                'order'     => $order
            ),
            'to' =>  $order->customer_email
        ));
        return $mail->send();
    }
}