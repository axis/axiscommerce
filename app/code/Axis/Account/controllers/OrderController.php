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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_OrderController extends Axis_Account_Controller_Abstract
{
    public function init()
    {
        parent::init();
        $this->addBreadcrumb(array(
            'label'      => Axis::translate('account')->__('My Orders'), 
            'controller' => 'order',
            'route'      => 'account'
        ));
    }

    public function indexAction()
    {
        $title = Axis::translate('account')->__('My Orders');
        $this->setTitle($title, $title, false);
        $this->view->orders = Axis::single('sales/order')->getOrdersByCustomer(
            Axis::getCustomerId()
        );
        $this->render();
    }

    public function viewAction()
    {
        $this->setTitle(Axis::translate('sales')->__('Order Information'));
        if ($this->_hasParam('orderId')) {
            $orderId = $this->_getParam('orderId');
        } else {
            $this->_redirect('/account/order');
        }

        $order = Axis::single('sales/order')->fetchAll(array (
            $this->db->quoteInto('customer_id = ?', Axis::getCustomerId()),
            $this->db->quoteInto('site_id = ?', Axis::getSiteId()),
            $this->db->quoteInto('id = ?', intval($orderId))
        ));

        if (!sizeof($order)) {
            $this->_redirect('/account/order');
        }
        $order = $order->current();

        $this->view->order = $order->toArray();
        $this->view->order['products'] = $order->getProducts();
        foreach($this->view->order['products'] as &$product) {
            // convert price with rates that was available
            // during order was created (not current rates)
            $product['price'] = Axis::single('locale/currency')
                ->from(
                    $product['price'] * $order->currency_rate,
                    $order->currency
                 );
            $product['final_price'] = Axis::single('locale/currency')
                ->from(
                    $product['final_price'] * $order->currency_rate,
                    $order->currency
                );
        }

        $this->view->order['totals'] = $order->getTotals();
        foreach ($this->view->order['totals'] as &$total) {
            // convert price with rates that was available
            // during order was created (not current rates)
            $total['value'] = Axis::single('locale/currency')->from(
                $total['value'] * $order->currency_rate, $order->currency
            );
        }

        $this->view->order['billing'] = $order->getBilling();
        $this->view->order['delivery'] = $order->getDelivery();
        // convert price with rates that was available
        // during order was created (not current rates)
        $this->view->order['order_total'] = Axis::single('locale/currency')
            ->from($order->order_total * $order->currency_rate, $order->currency);

        $this->render();
    }

    public function printAction()
    {
        if ($this->_hasParam('orderId')) {
            $orderId = $this->_getParam('orderId');
        } else {
            $this->_redirect('/account/order');
        }
        $order = Axis::single('sales/order')->fetchAll(array (
            $this->db->quoteInto('customer_id = ?', Axis::getCustomerId()),
            $this->db->quoteInto('site_id = ?', Axis::getSiteId()),
            $this->db->quoteInto('id = ?', intval($orderId))
        ));

        if (!sizeof($order)) {
            $this->_redirect('/account/order');
        }
        $order = $order->current();

        $this->view->order = $order->toArray();
        $this->view->order['products']  = $order->getProducts();
        $this->view->order['totals']    = $order->getTotals();
        $this->view->order['billing']   = $order->getBilling();
        $this->view->order['delivery']  = $order->getDelivery();

        $this->_helper->layout->setLayout('layout_print');
        $this->render();
    }
}