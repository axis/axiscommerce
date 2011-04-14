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
class Axis_Checkout_Controller_Checkout extends Axis_Core_Controller_Front_Secure
{
    /**
     * @return Axis_Checkout_Model_Checkout
     */
    protected function _getCheckout()
    {
        return Axis::single('checkout/checkout');
    }

    /**
     *
     * @return Axis_Account_Model_Form_Address
     */
    protected function _getAddressForm()
    {
        /**
         * @var Zend_Form $form
         */
        $form = Axis::model('account/form_address');
        $form->removeElement('default_billing');
        $form->removeElement('default_shipping');
        $form->setAction($this->view->href('/account/address-book/save', true));
        return $form;
    }

    /* TODO move to address model */
    protected function _checkAddress($addressId)
    {
        if (!$addressId) {
            return false;
        }
        $address = Axis::single('account/customer_address')
            ->find($addressId)
            ->current()
        ;
        if (!$address instanceof Axis_Db_Table_Row
            || $address->customer_id != Axis::getCustomerId()) {

            return false;
        }
        return true;
    }

    protected function _validateCart()
    {
        if (!$this->_getCheckout()->getCart()->getCount() ||
            !$this->_getCheckout()->getCart()->validateContent()) {

            $this->_redirect('checkout/cart');
            return;
        }
    }

    public function addressListAction()
    {
        $type = $this->_getParam('type');

        $customerId = Axis::getCustomerId();

        switch ($type) {
            case 'billing-address':
                $this->setTitle(
                    Axis::translate('checkout')->__(
                        'Select billing address'
                ));
                $billing = $this->_getCheckout()->getBilling();
                if ($billing instanceof Axis_Address && $billing->hasId()) {
                    $selectedAddressId = $this->_getCheckout()->getBilling()->id;
                } else {
                    $selectedAddressId = false;
                    if ($customer = Axis::single('account/customer')->find($customerId)->current()) {
                        $selectedAddressId = $customer->default_billing_address_id;
                    }
                }
                break;
            case 'delivery-address':
                $this->setTitle(
                    Axis::translate('checkout')->__(
                        'Select delivery address'
                ));
                $delivery = $this->_getCheckout()->getDelivery();
                if ($delivery instanceof Axis_Address && $delivery->hasId()) {
                    $selectedAddressId = $this->_getCheckout()->getDelivery()->id;
                } else {
                    $selectedAddressId = false;
                    if ($customer = Axis::single('account/customer')->find($customerId)->current()) {
                        $selectedAddressId = $customer->default_shipping_address_id;
                    }
                }
                break;
            default:
                $selectedAddressId = 0;
        }

        $this->view->addressList =  Axis::single('account/customer_address')
            ->getSortListByCustomerId($customerId);
        $this->view->assign(array(
            'selectedAddressId' => $selectedAddressId,
            'addressType'       => $type
        ));

        $this->render();
    }

    public function shippingMethodAction()
    {
        $this->view->shippingMethods = Axis_Shipping::getAllowedMethods(
            $this->_getCheckout()->getShippingRequest()
        );
        $this->render('shipping');
    }

    public function paymentMethodAction()
    {
        $this->view->paymentMethods = Axis_Payment::getAllowedMethods(
            $this->_getCheckout()->getPaymentRequest()
        );
        $this->render('payment');
    }

    public function confirmationAction()
    {
        $checkout = $this->_getCheckout();
        if (!$checkout->isValid()) {
            $this->view->checkoutErrors = $checkout->getErrors();
            $this->render('checkout-errors');
            return;
        }
        $this->view->checkout = array(
            'delivery'      => $checkout->getDelivery(),
            'billing'       => $checkout->getBilling(),
            // read payment data from session storage
            'payment'       => $checkout->payment()->getTitle(),
            'payment_code'  => $checkout->payment()->getCode(),
            // read shipping data from session storage
            'shipping'      => $checkout->shipping()->getTitle(),
            'shipping_code' => $checkout->shipping()->getCode(),
            'products'      => $checkout->getCart()->getProducts(),
            'totals'        => $checkout->getTotal()->getCollects(),
            'total'         => $checkout->getTotal()->getTotal()
        );
        $this->render();
    }

    public function processAction()
    {
        $this->_forward('process', 'index', 'Axis_Checkout');
    }
}