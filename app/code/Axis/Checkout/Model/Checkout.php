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
 * @copyright   Copyright 2008-2011 Axis
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

    public function clean()
    {
        if ($paymentMethod = $this->payment()) {
            $paymentMethod->clear();
        }
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
        if (!$this->_total instanceof Axis_Checkout_Model_Total) {
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

    /**
     * @return array
     */
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
     * Update payment request information
     *
     * @param mixed $key
     * @param mixed $value
     * @return Axis_Checkout_Model_Checkout
     */
    public function addPaymentRequestData($key, $value = null)
    {
        if (!Zend_Registry::isRegistered('paymentRequest')) {
            return $this;
        }

        $request = $this->getPaymentRequest();
        if (is_array($key)) {
            $request = array_merge($request, $key);
        } else {
            $request[$key] = $value;
        }
        Zend_Registry::set('paymentRequest', $request);
        return $this;
    }

    /**
     * Update shipping request information
     *
     * @param mixed $key
     * @param mixed $value
     * @return Axis_Checkout_Model_Checkout
     */
    public function addShippingRequestData($key, $value = null)
    {
        if (!Zend_Registry::isRegistered('shippingRequest')) {
            return $this;
        }

        $request = $this->getShippingRequest();
        if (is_array($key)) {
            $request = array_merge($request, $key);
        } else {
            $request[$key] = $value;
        }
        Zend_Registry::set('shippingRequest', $request);
        return $this;
    }

    /**
     * Return current shipping module instance
     *
     * @return Axis_Method_Shipping_Model_Abstract|null
     */
    public function shipping()
    {
        $code = $this->getShippingMethodCode();
        if (null === $this->_shipping && null !== $code) {
            $this->_shipping = Axis_Shipping::getMethod($code);
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
        $code = $this->getPaymentMethodCode();
        if (null === $this->_payment && null !== $code) {
            $this->_payment = Axis_Payment::getMethod($code);
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
        $this->addShippingRequestData('payment_method_code', $code);
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
        $this->addPaymentRequestData('shipping_method_code', $code);
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
        if (is_array($address)) {
            $defaultAddress = $this->getDefaultAddress();
            if (!empty($address['state'])) {
                unset($defaultAddress['zone_id']);
            }
            $address = array_merge(
                $defaultAddress,
                $address
            );
        }

        $address = Axis::single('account/customer_address')
            ->getAddress($address);

        if (!$address) {
            return false;
        }

        $this->getStorage()->delivery = $address;
        $this->addShippingRequestData($address->toArray());
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
        if (is_array($address)) {
            $defaultAddress = $this->getDefaultAddress();
            if (!empty($address['state'])) {
                unset($defaultAddress['zone_id']);
            }
            $address = array_merge(
                $defaultAddress,
                $address
            );
        }

        $address = Axis::single('account/customer_address')
            ->getAddress($address);

        if (!$address) {
            return false;
        }

        $this->getStorage()->billing = $address;
        $this->addPaymentRequestData($address->toArray());
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
     * Add default data to storage:
     *  Billing and Delivery addresses
     *  Payment and Shipping methods
     *
     * @return Axis_Checkout_Model_Checkout
     */
    public function applyDefaults()
    {
        // addresses
        $defaultAddress = $this->getDefaultAddress();
        $customer       = Axis::getCustomer();

        // if (!$this->getBilling()) {
            if ($customer && $customer->default_billing_address_id) {
                $this->setBilling($customer->default_billing_address_id);
            } else {
                $this->setBilling($defaultAddress);
            }
        // }

        // if (!$this->getDelivery()) {
            $billingAddress = $this->getBilling();
            if ($customer && $customer->default_shipping_address_id) {
                if ($billingAddress->id == $customer->default_shipping_address_id) {
                    $this->getStorage()->delivery = $billingAddress;
                } else {
                    $this->setDelivery($customer->default_shipping_address_id);
                }
            } else {
                $this->getStorage()->delivery = $billingAddress;
            }
        // }

        $config = Axis::config('checkout/default_values');
        try {
            $this->setShippingMethod($config->shipping_method);
            $this->setPaymentMethod($config->payment_method);
        } catch (Exception $e) {

        }

        return $this;
    }

    /**
     * Retrieve the default address data from checkout/address_form config
     *
     * @return array
     * <pre>
     *  :id         => 0,
     *  :country_id => int
     *  :zone_id    => int
     *  :postcode   => string
     *  :firstname  => string
     *  :lastname   => string
     * </pre>
     */
    public function getDefaultAddress()
    {
        $defaults = Axis::model('account/customer_address')->getDefaultValues();
        $customer = Axis::getCustomer();
        return array_merge($defaults, array(
            'id'            => 0,
            'firstname'     => $customer ? $customer->firstname : '',
            'lastname'      => $customer ? $customer->lastname  : ''
        ));
    }

    /**
     * Apply Shipping method if possible
     *
     * @param mixed $data
     * @return void
     * @throws Axis_Exception
     */
    public function setShippingMethod($data)
    {
        if (!is_array($data)) {
            $data = array(
                'method' => $data
            );
        }

        if (empty($data['method'])) {
            return $this->setShippingMethodCode(null);
        }

        // methodCode can include method type also - Pickup_Standard|Ups_Standard_WXS
        list($moduleName, $methodName) = explode('_', $data['method']);
        if (!in_array($moduleName . '_' . $methodName, Axis_Shipping::getMethodNames())) {
            throw new Axis_Exception(
                Axis::translate('checkout')->__(
                    "'%s' method not found among installed modules", $data['method']
                )
            );
        }

        $method = Axis_Shipping::getMethod($data['method']);
        if (!$method instanceof Axis_Method_Shipping_Model_Abstract
            || !$method->isAllowed($this->getShippingRequest())) {

            throw new Axis_Exception(
                Axis::translate('checkout')->__(
                    'Selected shipping method is not allowed'
                )
            );
        }

        $this->_shipping = $method;
        $this->setShippingMethodCode($data['method']);
    }

    /**
     * Apply Payment method if possible
     *
     * @param mixed $data
     * @return void
     * @throws Axis_Exception
     */
    public function setPaymentMethod($data)
    {
        if (!is_array($data)) {
            $data = array(
                'method' => $data
            );
        }

        if (empty($data['method'])) {
            return $this->setPaymentMethodCode(null);
        }

        if (!in_array($data['method'], Axis_Payment::getMethodNames())) {
            throw new Axis_Exception(
                Axis::translate('checkout')->__(
                    "'%s' method not found among installed modules", $data['method']
                )
            );
        }

        $method = Axis_Payment::getMethod($data['method']);
        if (!$method instanceof Axis_Method_Payment_Model_Abstract
            || !$method->isEnabled()
            || !$method->isAllowed($this->getPaymentRequest())) {

            throw new Axis_Exception(
                Axis::translate('checkout')->__(
                    'Selected payment method is not allowed'
                )
            );
        }

        $this->setPaymentMethodCode($data['method']);
        $this->_payment = $method;
        $this->payment()->saveData($data);
    }

    /**
     * Validates and apply the supplied billing address
     *
     * @param array $data
     * @param bool $validateForm
     * @return void
     * @throws Axis_Exception
     */
    public function setBillingAddress(array $data, $validateForm = true)
    {
        if (!empty($data['id'])) {
            if (!$this->setBilling($data['id'])
                || $this->getBilling()->customer_id != Axis::getCustomerId()) {

                throw new Axis_Exception(
                    Axis::translate('checkout')->__('Invalid address')
                );
            }

            if ($data['use_for_delivery']) {
                $this->setDelivery($data['id']);
            }
            return;
        }

        $formAddress = $this->getAddressForm('billing');
        if (!empty($data['register']) && !Axis::getCustomerId()) {
            $formAddress->addRegistrationFields();
        }
        if ($validateForm && !$formAddress->isValid($data)) {
            foreach ($formAddress->getMessages() as $messages) {
                foreach ($messages as $fieldName => $message) {
                    Axis::message()->addError($fieldName . ': ' . current($message));
                }
            }
            throw new Axis_Exception();
        }

        $this->setBilling($data);
        if ($data['use_for_delivery']) {
            $this->setDelivery($data);
        }
    }

    /**
     * Validates and apply the supplied delivery address
     *
     * @param array $data
     * @param bool $validateForm
     * @return void
     * @throws Axis_Exception
     */
    public function setDeliveryAddress(array $data, $validateForm = true)
    {
        if (!empty($data['id'])) {
            if (!$this->setDelivery($data['id'])
                || $this->getDelivery()->customer_id != Axis::getCustomerId()) {

                throw new Axis_Exception(
                    Axis::translate('checkout')->__('Invalid address')
                );
            }
            return;
        }

        $formAddress = $this->getAddressForm('delivery');
        if ($validateForm && !$formAddress->isValid($data)) {
            foreach ($formAddress->getMessages() as $messages) {
                foreach ($messages as $fieldName => $message) {
                    Axis::message()->addError($fieldName . ': ' . current($message));
                }
            }
            throw new Axis_Exception();
        }
        $this->setDelivery($data);
    }

    /**
     * @param string $type billing|delivery
     * @return Axis_Checkout_Model_Form_Address
     */
    public function getAddressForm($type = 'billing')
    {
        $addressForm = Axis::model('checkout/form_address', array(
            'subform' => $type . '_address'
        ));
        $addressForm->populate($this->{'get'.ucfirst($type)}()->toFlatArray());
        $addressForm->removeDecorator('Form');
        $addressForm->removeDecorator('HtmlTag');

        return $addressForm;
    }
}
