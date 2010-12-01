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
 * @subpackage  Axis_Checkout_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Checkout extends Axis_Object
{
    /**
     * @var Zend_Session_Namespace
     */
    private $_storage;

    /**
     * @var Axis_Checkout_Model_Total
     */
    private $_total;

    /**
     * @var Axis_Checkout_Model_Cart
     */
    private $_cart;

    /**
     * @var Axis_Method_Shipping_Model_Abstract
     */
    private $_shipping;

    /**
     * @var Axis_Payment_Model_Method_Abstract
     */
    private $_payment;

    private $_errors;

    const ERROR_CUSTOMER  = 'unknown_customer';
    const ERROR_DELIVERY  = 'unknown_delivery';
    const ERROR_SHIPPPING = 'unknown_shipping';
    const ERROR_BILLING   = 'unknown_billing';
    const ERROR_PAYMENT   = 'unknown_payment';

    public function clean()
    {
        $this->getStorage()->unsetAll();
    }

    /**
     * Return current shopping cart
     *
     * @return Axis_Checkout_Model_Cart
     */
    public function getCart()
    {
        if (!$this->_cart instanceof Axis_Checkout_Model_Cart) {
            $this->_cart = Axis::single('checkout/cart');
        }
        return $this->_cart;
    }

    /**
     * Return current Checkout_Total model
     *
     * @return Axis_Checkout_Model_Total
     */
    public function getTotal()
    {
        if (!$this->_total instanceof  Axis_Checkout_Model_Total) {
            $this->_total = Axis::single('checkout/total');
        }
        return $this->_total;
    }

    /**
     * Return checkout data storage
     *
     * @return Zend_Session_Namespace
     */
    public function getStorage()
    {
        if (!$this->_storage instanceof Zend_Session_Namespace) {
            $this->_storage = new Zend_Session_Namespace('Checkout');
        }
        return $this->_storage;
    }

    /**
     * Return information, that is needed by shipping methods
     *
     * @return array
     */
    public function getShippingRequest()
    {
        if (!Zend_Registry::isRegistered('shippingRequest')) {
            $request = (null === $this->getDelivery()) ?
                array() : $this->getDelivery()->toArray();

            $request['boxes']  = 1;
            $request['qty']    = $this->getCart()->getCount();
            $request['weight'] = $this->getCart()->getTotalWeight();
            $request['price']  = $this->getTotal()->getTotal('subtotal');
            $request['currency'] = Axis::config('locale/main/baseCurrency');
            $request['payment_method_code'] = $this->getPaymentMethodCode();
            Zend_Registry::set('shippingRequest', $request);
        }
        return Zend_Registry::get('shippingRequest');
    }

    public function getPaymentRequest()
    {
        if (!Zend_Registry::isRegistered('paymentRequest')) {
            $request = (null === $this->getBilling()) ?
                array() : $this->getBilling()->toArray();

            $request['boxes']  = 1;
            $request['qty']    = $this->getCart()->getCount();
            $request['weight'] = $this->getCart()->getTotalWeight();
            $request['price']  = $this->getTotal()->getTotal();
            $request['currency'] = Axis::config('locale/main/baseCurrency');
            $request['shipping_method_code'] = $this->getShippingMethodCode();
            Zend_Registry::set('paymentRequest', $request);
        }
        return Zend_Registry::get('paymentRequest');
    }

    /**
     * Return current shipping module instance
     *
     * @return Axis_Method_Shipping_Model_Abstract|null
     */
    public function shipping()
    {
        if (null === $this->_shipping && null !== $this->getShippingMethodCode()) {
            $this->_shipping = Axis_Shipping::getMethod(
                $this->getShippingMethodCode()
            );
        }
        return $this->_shipping;
    }

    /**
     * Return current payment module instance
     *
     * @return Axis_Payment_Model_Method_Abstract|null
     */
    public function payment()
    {
        if (null === $this->_payment && null !== $this->getPaymentMethodCode()) {
            
            $this->_payment = Axis_Payment::getMethod(
                $this->getPaymentMethodCode()
            );
        }
        return $this->_payment;
    }

    /**
     * Set payment method code to session storage
     *
     * @param string $code Payment Method code
     */
    public function setPaymentMethodCode($code)
    {
        $this->getStorage()->payment = $code;
    }

    /**
     * Get payment method code from session storage
     *
     * @return string $code Payment Method code
     */
    public function getPaymentMethodCode()
    {
        return $this->getStorage()->payment;
    }

    /**
     * Set shipping method to session storage
     *
     * @param string $code Shipping Method code
     */
    public function setShippingMethodCode($code)
    {
        $this->getStorage()->shipping = $code;
    }

    /**
     * Get shipping method code from session storage
     *
     * @return string $code Shipping Method code
     */
    public function getShippingMethodCode()
    {
        return $this->getStorage()->shipping;
    }

    /**
     * Set delivery address
     *
     * @param int|array mixed $address Address id or address array
     * @return bool
     */
    public function setDelivery($address)
    {
        $address = Axis::single('account/customer_address')
            ->getAddress($address);
        if (!$address) {
            return false;
        }

        $this->getStorage()->delivery = $address;
        return true;
    }

    /**
     * get delivery address
     *
     * @return Axis_Address
     */
    public function getDelivery()
    {
        return $this->getStorage()->delivery;
    }

    /**
     * Set billing address
     *
     * @param int|array mixed $address Address id or address array
     * @return bool
     */
    public function setBilling($address)
    {
        $address = Axis::single('account/customer_address')
            ->getAddress($address);
        if (!$address) {
            return false;
        }

        $this->getStorage()->billing = $address;
        return true;
    }

    /**
     * get billing address
     *
     * @return Axis_Address
     */
    public function getBilling()
    {
        return $this->getStorage()->billing;
    }

    /**
     * @return void
     */
    public function setOrderId($orderId)
    {
        $this->getStorage()->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getStorage()->orderId;
    }

    /**
     * Validate that all data was initialized
     *
     * @return boolean
     */
    public function isValid()
    {
        $storage = $this->getStorage();
        $this->_errors = array();
        if (!Axis::getCustomerId() && !$this->getStorage()->asGuest) {
            $this->_errors[] = array(
                'code' => self::ERROR_CUSTOMER,
                'text' => 'Unknown customer. Sorry but you must start checkout process again.'
            );
        }
        if (!isset($storage->delivery)) {
            $this->_errors[] = array(
                'code' => self::ERROR_DELIVERY,
                'text' => 'Empty shipping address'
            );
        }
        if (!isset($storage->shipping)) {
            $this->_errors[] = array(
                'code' => self::ERROR_SHIPPPING,
                'text' => 'Empty shipping method'
            );
        }
        if (!isset($storage->billing)) {
            $this->_errors[] = array(
                'code' => self::ERROR_BILLING,
                'text' => 'Empty billing address'
            );
        }
        if (!isset($storage->payment)) {
            $this->_errors[] = array(
                'code' => self::ERROR_PAYMENT,
                'text' => 'Empty payment method'
            );
        }

        if (count($this->_errors)) {
            return false;
        }

        return true;
    }

    /**
     * Return errors from check method
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}