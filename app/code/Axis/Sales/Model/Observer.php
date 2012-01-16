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
 * @copyright   Copyright 2008-2012 Axis
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
        try {
            $mail = new Axis_Mail();
            $mail->setLocale(Axis::config('locale/main/language_admin'));
            $mail->setConfig(array(
                'event'   => 'order_new-owner',
                'subject' => Axis::translate('sales')->__('Order created'),
                'data'    => array(
                    'order' => $order
                ),
                'to' => Axis_Collect_MailBoxes::getName(
                    Axis::config('sales/order/email')
                )
            ));
            $mail->send();
            return true;
        } catch (Zend_Mail_Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return bool
     */
    public function notifyCustomerNewOrder(Axis_Sales_Model_Order_Row $order)
    {
        try {
            $mail = new Axis_Mail();
            $configResult = $mail->setConfig(array(
                'event'   => 'order_new-customer',
                'subject' => Axis::translate('sales')->__('Your order'),
                'data'    => array(
                    'order' => $order
                ),
                'to' => $order->customer_email,
                'attachments' => array(
                    'invoice.html' => 'sales/invoice_print.phtml'
                )
            ));
            $mail->send();

//            if ($configResult) {
//                Axis::message()->addSuccess(
//                    Axis::translate('core')->__('Mail was sended successfully')
//                );
//            }
            return true;
        } catch (Zend_Mail_Exception $e) {
            Axis::message()->addError(
                Axis::translate('core')->__('Mail sending was failed.')
            );
            return false;
        }
    }

    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'sales' => array(
                'label'         => 'Sales',
                'order'         => 30,
                'translator'    => 'Axis_Sales',
                'uri'           => '#',
                'pages' => array(
                    'sales/order' => array(
                        'label'         => 'Orders',
                        'order'         => 10,
                        'module'        => 'Axis_Sales',
                        'controller'    => 'order',
                        'route'         => 'admin/axis/sales',
                        'resource'      => 'admin/axis/sales/order'
                    )
                )
            ),
            'locale' => array(
                'pages' => array(
                    'sales/order-status' => array(
                        'label'         => 'Order statuses',
                        'order'         => 30,
                        'translator'    => 'Axis_Sales',
                        'module'        => 'Axis_Sales',
                        'controller'    => 'order-status',
                        'route'         => 'admin/axis/sales',
                        'resource'      => 'admin/axis/sales/order-status'
                    )
                )
            )
        ));
    }
}
