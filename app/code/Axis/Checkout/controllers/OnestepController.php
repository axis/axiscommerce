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
class Axis_Checkout_OnestepController extends Axis_Checkout_Controller_Checkout
{
    protected $_sections = array(
        'shipping-method'   => 'checkout/onestep/shipping-method/available.phtml',
        'payment-method'    => 'checkout/onestep/payment-method/available.phtml',
        'shopping-cart'     => 'checkout/onestep/review/cart.phtml',
    );

    public function indexAction()
    {
        $this->_validateCart();

        $title = Axis::translate('checkout')->__('Checkout Process');
        $this->setTitle($title, $title, false);

        $checkout = $this->_getCheckout()->applyDefaults();

        $formBillingAddress = $checkout->getAddressForm('billing');
        if (!Axis::getCustomerId()) {
            $formBillingAddress->addRegistrationFields();
        }

        $this->view->checkout = array(
            'form_billing_address'  => $formBillingAddress,
            'form_delivery_address' => $checkout->getAddressForm('delivery'),
            'address_list'      => Axis::model('account/customer_address')
                                        ->getSortListByCustomerId(),
            'delivery_address'  => $checkout->getDelivery(),
            'billing_address'   => $checkout->getBilling(),
            'shipping_methods'  => Axis_Shipping::getAllowedMethods(
                $checkout->getShippingRequest()
            ),
            'payment_methods'   => Axis_Payment::getAllowedMethods(
                $checkout->getPaymentRequest()
            ),
            'products'      => $checkout->getCart()->getProducts(),
            'totals'        => $checkout->getTotal()->getCollects(),
            'total'         => $checkout->getTotal()->getTotal()
        );

        $this->_helper->layout->setLayout('layout_1column');
        $this->render();
    }

    /**
     * Applies billing and delivery (if use for delivery) address to the order.
     * Updates available shipping and payment methods
     * Unset selected methods if they cannot be selected
     */
    public function updateBillingAddressAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout   = $this->_getCheckout();
        $billing    = $this->_getParam('billing_address');
        try {
            $checkout->setBillingAddress($billing, false);
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                Axis::message()->addError($message);
            }
            return $this->_helper->json->sendFailure();
        }

        $sections = array();
        $this->view->checkout = array();

        if ($billing['use_for_delivery']) {
            // if current shipping is not available - reset shipping
            $shippingMethods = Axis_Shipping::getAllowedMethods(
                $checkout->getShippingRequest()
            );
            if (!$this->_isAvailableShippingMethod(
                    $checkout->getShippingMethodCode(),
                    $shippingMethods['methods']
                )) {

                $checkout->setShippingMethod(null);
                $shippingMethods['currentMethodCode'] = null;
            }

            $sections = array('shipping-method', 'shopping-cart');
            $this->view->checkout = array(
                'shipping_methods' => $shippingMethods,
                'products'  => $checkout->getCart()->getProducts(),
                'totals'    => $checkout->getTotal()->getCollects(),
                'total'     => $checkout->getTotal()->getTotal()
            );
        }

        $paymentMethods = Axis_Payment::getAllowedMethods(
            $checkout->getPaymentRequest()
        );
        $sections[] = 'payment-method';
        $this->view->checkout['payment_methods'] = $paymentMethods;

        return $this->_helper->json->sendSuccess(array(
            'sections' => $this->_renderSections($sections)
        ));
    }

    /**
     * Applies delivery address to the order.
     * Updates available shipping and payment methods
     * Unset selected methods if they cannot be selected
     */
    public function updateDeliveryAddressAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout   = $this->_getCheckout();
        $billing    = $this->_getParam('billing_address');
        try {
            $delivery = $billing['use_for_delivery'] ?
                $billing : $this->_getParam('delivery_address');
            $checkout->setDeliveryAddress($delivery, false);
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                Axis::message()->addError($message);
            }
            return $this->_helper->json->sendFailure();
        }

        // if current shipping is not available - reset shipping
        $shippingMethods = Axis_Shipping::getAllowedMethods(
            $checkout->getShippingRequest()
        );
        if (!$this->_isAvailableShippingMethod(
                $checkout->getShippingMethodCode(),
                $shippingMethods['methods']
            )) {

            $checkout->setShippingMethod(null);
            $shippingMethods['currentMethodCode'] = null;
        }

        $paymentMethods = Axis_Payment::getAllowedMethods(
            $checkout->getPaymentRequest()
        );
            // Zend_Debug::dump($paymentMethods);die;
        $this->view->checkout = array(
            'payment_methods'   => $paymentMethods,
            'shipping_methods'  => $shippingMethods,
            'products'  => $checkout->getCart()->getProducts(),
            'totals'    => $checkout->getTotal()->getCollects(),
            'total'     => $checkout->getTotal()->getTotal()
        );

        return $this->_helper->json->sendSuccess(array(
            'sections' => $this->_renderSections(array(
                'shipping-method',
                'shopping-cart',
                'payment-method'
            ))
        ));
    }

    /**
     * Applies shipping method to the order and updates available payment methods section
     * There is no need to check the availability of current payment method
     */
    public function updateShippingMethodAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout = $this->_getCheckout();
        try {
            $checkout->setShippingMethod($this->_getParam('shipping', array()));
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                Axis::message()->addError($message);
            }
            return $this->_helper->json->sendFailure();
        }

        $paymentMethods = Axis_Payment::getAllowedMethods(
            $checkout->getPaymentRequest()
        );
        $this->view->checkout = array(
            'payment_methods' => $paymentMethods,
            'products'  => $checkout->getCart()->getProducts(),
            'totals'    => $checkout->getTotal()->getCollects(),
            'total'     => $checkout->getTotal()->getTotal()
        );

        return $this->_helper->json->sendSuccess(array(
            'sections' => $this->_renderSections(
                array(
                    'payment-method',
                    'shopping-cart'
                )
            )
        ));
    }

    /**
     * Applies payment method to the order and updates available shipping methods section
     * There is no need to check the availability of current shipping method
     */
    public function updatePaymentMethodAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout = $this->_getCheckout();
        try {
            $checkout->setPaymentMethod($this->_getParam('payment', array()));
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                Axis::message()->addError($message);
            }
            return $this->_helper->json->sendFailure();
        }

        $shippingMethods = Axis_Shipping::getAllowedMethods(
            $checkout->getShippingRequest()
        );
        $this->view->checkout = array(
            'shipping_methods' => $shippingMethods
        );

        return $this->_helper->json->sendSuccess(array(
            'sections' => $this->_renderSections(
                array(
                    'shipping-method'
                )
            )
        ));
    }

    /**
     * Updates shopping cart totals, available shipping and payment methods
     * Unset shipping and payment methods if needed
     */
    public function updateShoppingCartAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout = $this->_getCheckout();
        $cart     = Axis::single('checkout/cart');
        foreach ($this->_getParam('quantity') as $itemId => $quantity) {
            $cart->updateItem($itemId, $quantity);
        }

        $products = $checkout->getCart()->getProducts();
        if (!count($products)) {
            return $this->_helper->json->sendSuccess(array(
                'redirect' => $this->view->href('checkout/cart', true)
            ));
        }

        $shippingMethods = Axis_Shipping::getAllowedMethods(
            $checkout->getShippingRequest()
        );
        $paymentMethods = Axis_Payment::getAllowedMethods(
            $checkout->getPaymentRequest()
        );
        $this->view->checkout = array(
            'shipping_methods' => $shippingMethods,
            'payment_methods'  => $paymentMethods,
            'products'         => $products,
            'totals'           => $checkout->getTotal()->getCollects(),
            'total'            => $checkout->getTotal()->getTotal()
        );

        return $this->_helper->json->sendSuccess(array(
            'sections' => $this->_renderSections(
                array(
                    'shopping-cart',
                    'shipping-method',
                    'payment-method'
                )
            )
        ));
    }

    /**
     * Create order action
     */
    public function processAction()
    {
        $this->_helper->layout->disableLayout();

        $checkout = $this->_getCheckout();
        $billing  = $this->_getParam('billing_address');
        $delivery = $this->_getParam('delivery_address');
        try {
            $checkout->setBillingAddress($billing);
            if (!$billing['use_for_delivery']) {
                $checkout->setDeliveryAddress($delivery);
            }
            $checkout->setShippingMethod($this->_getParam('shipping'));
            $checkout->setPaymentMethod($this->_getParam('payment'));

            $storage = $checkout->getStorage();
            $storage->customer_comment = $this->_getParam('comment');

            $result = (array)$checkout->payment()->preProcess();

            if (empty($result['redirect'])) {
                $order = Axis::single('sales/order')->createFromCheckout();
                $checkout->setOrderId($order->id);
                $postProcess = (array)$checkout->payment()->postProcess($order);
                $result = array_merge($result, $postProcess);

                Axis::dispatch('checkout_place_order_after', $order);
            }
        } catch (Exception $e) {
            Axis::dispatch('sales_order_create_failed', array('exception' => $e));
            $message = $e->getMessage();
            if (!empty($message)) {
                Axis::message()->addError($message);
            }
            return $this->_helper->json->sendFailure();
        }

        if (empty($result['redirect'])) {
            $result['redirect'] = $this->view->href('checkout/success', true);
        }

        $this->_helper->json->sendJson(array(
            'redirect'  => $result['redirect'],
            'success'   => true
        ), false, false);
    }

    /**
     * Renders dynamicly updated sections
     *
     * @param array $sections
     * @return array
     */
    protected function _renderSections(array $sections)
    {
        $result = array();
        foreach ($sections as $section) {
            $result[$section] = $this->view->render($this->_sections[$section]);
        }
        return $result;
    }

    /**
     * Searches for shipping method among received methods
     *
     * @return boolean
     */
    protected function _isAvailableShippingMethod($method, $methods)
    {
        if (!$method) {
            return true;
        }
        foreach ($methods as $code => $types) {
            foreach ($types as $type) {
                if ($type['id'] == $method) {
                    return true;
                }
            }
        }
        return false;
    }
}
