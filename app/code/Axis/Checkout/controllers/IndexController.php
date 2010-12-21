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
 * @copyright   Copyright 2008-2010 Axis
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
        $this->view->crumbs()->add(
            Axis::translate('checkout')->__(
                'Checkout'
            ),
            '/checkout/cart'
        );
    }

    public function indexAction()
    {
        $this->_redirect('/checkout/onepage');
    }

    // @todo parent class forward to child method :D
    public function processAction()
    {
        $this->_helper->layout->disableLayout();
        $checkout = $this->_getCheckout();

        if (!$checkout->isValid()) {
            foreach ($checkout->getErrors() as $error) {
               Axis::message()->addError($error['text']);
            }
            $this->_redirect('/checkout/onepage');
        }

        $storage = $checkout->getStorage();

        if ($storage->registerGuest) {

            $form = Axis::single('account/form_signup');
            /**
             * @var Axis_Account_Model_Form_Signup $form
             */
            $billing = $checkout->getBilling();

            if (!$billing->hasPassword()
                && $billing->hasRegisterPassword()) {

                $billing->password = $billing->register_password;
                $billing->password_confirm = $billing->register_password;
                $billing->register_password = null;
            }

            if ($form->isValid($billing->toFlatArray())) {
                $mCustomer = Axis::single('account/customer');
                $customerData = $billing->toFlatArray();
                $customerData['site_id'] = Axis::getSiteId();
                $customerData['is_active'] = 1;
                $mCustomer->save($customerData);
                $mCustomer->login($billing->email, $billing->password);
                if ($customerId = Axis::getCustomerId()) {
                    $billing->customer_id = $customerId;
                    Axis::single('account/customer')
                        ->find($customerId)
                        ->current()
                        ->setAddress($billing->toFlatArray());
                }
            }
        }
        /* Receive payment */
        try {
            $checkout->payment()->preProcess();
            /* create order */
            $order = Axis::single('sales/order')->createFromCheckout();

            Axis::dispatch('sales_order_create_success', $order);

            $checkout->setOrderId($order->id);

            $checkout->payment()->postProcess($order);

            $this->render();
            $checkout->payment()->clear();
            $this->_redirect('/checkout/success');

        } catch (Exception $e) {
            Axis::dispatch('sales_order_create_failed', array('exception' => $e));
            $message = $e->getMessage();
            if (!empty($message)) {
                Axis::message()->addError($message);
                error_log($message);
            }
            $this->_redirect('/checkout/cart');
            return;
        }
    }

    public function successAction()
    {
        $this->view->pageTitle = Axis::translate('checkout')->__(
            'Checkout Success'
        );
         /* analytic ZA4OT*/
        Axis::config()->analytics->main->checkoutSuccess = true;

        if (!$orderId = $this->_getCheckout()->getOrderId()) {
            $this->_redirect('/checkout/onepage');
        }
        $this->view->orderId = $orderId;
        $this->_getCheckout()->clean();

        $this->render();
    }

    public function cancelAction()
    {
        $this->_getCheckout()->clean();
        $this->_redirect('/checkout/cart');
    }
}