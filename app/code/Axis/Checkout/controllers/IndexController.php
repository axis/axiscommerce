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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_IndexController extends Axis_Checkout_Controller_Checkout
{
    /**
     * Return Checkout Model
     *
     * @return Axis_Checkout_Model_Checkout
     */
    public function init()
    {
        parent::init();
        $this->_helper->breadcrumbs(array(
            'label'      => Axis::translate('checkout')->__('Checkout'),
            'controller' => 'cart',
            'route'      => 'checkout'
        ));
    }

    public function indexAction()
    {
        $this->_redirect('checkout/onestep');
    }

    public function successAction()
    {
        $this->setTitle(Axis::translate('checkout')->__('Checkout Success'));
        Axis::config()->analytics->main->checkoutSuccess = true;

        $checkout   = $this->_getCheckout();
        $orderId    = $checkout->getOrderId();
        $order      = Axis::model('sales/order')->find($orderId)->current();
        if (!$order instanceof Axis_Sales_Model_Order_Row) {
            $this->_redirect('checkout/onestep');
        }

        if (!$statusId = $checkout->payment()->config('orderStatusId')) {
            $statusId = Axis::config('sales/order/defaultStatusId');
        }
        if ($statusId != $order->getStatus()) {
            $order->setStatus($statusId);
        }

        Axis::dispatch('sales_order_create_success', $order);
        $this->view->order = $order;
        $checkout->getCart()->clear();
        $checkout->clean();

        $this->render();
    }

    public function cancelAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout   = $this->_getCheckout();
        $orderId    = $checkout->getOrderId();
        if ($orderId
            && $order = Axis::model('sales/order')->find($orderId)->current()) {

            $order->setStatus('cancel');
        }
        $checkout->clean();

        $this->_redirect('checkout/cart');
    }
}
