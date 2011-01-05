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
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentPaypal_ExpressCheckoutController extends Axis_Checkout_Controller_Checkout
{

    /**
     *
     * @var Axis_PaymentPaypal_Model_Express
     */
    private $_payment;

    /**
     *
     * @return Axis_PaymentPaypal_Model_Express
     */
    protected function _getPayment()
    {
        return $this->_payment;
    }

    protected function _getAddressForm()
    {
        $form = Axis::model('account/Form_Address');
        $form->removeElement('default_billing');
        $form->removeElement('default_shipping');
        $form->removeElement('address_submit');
        return $form;
    }

    protected function _checkAddress($addressId)
    {
        if (!$addressId) {
            return false;
        }
        /*
         * Check if address assigned to this customer
         */
        $address = Axis::single('account/customer_address')
            ->find($addressId)
            ->current();

        if (!$address instanceof Axis_Db_Table_Row
            || $address->customer_id != Axis::getCustomerId()) {

            return false;
        }
        return true;
    }

    public function init()
    {
        parent::init();

        $this->_payment = Axis::single('paymentPaypal/express');
        if (!$this->_getPayment()->isEnabled()) {
            $this->_redirect('/checkout/cart');
        }
    }

    public function indexAction()
    {
        //SetExpressCheckout get token
        if (!$this->_getPayment()->runSetExpressCheckout()) {
            $this->_redirect('/checkout/cart');
        }

        $this->_getCheckout()->setPaymentMethodCode(
            $this->_getPayment()->getCode()
        );

        $useraction = '&useraction=continue';
        if (true == $this->_getPayment()->getStorage()->markflow) {
            $useraction = '&useraction=commit';
        }

        $this->_redirect(
            $this->_getPayment()->getPayPalLoginServer()
                . "?cmd=_express-checkout&token="
                . $this->_getPayment()->getStorage()->token
                . $useraction,
            array(),
            false
        );
    }

    public function detailsAction()
    {
        if (empty($this->_getPayment()->getStorage()->token)) {
            $this->_redirect('/paymentpaypal/express-checkout/index');
        }

        $response = $this->_getPayment()->runGetExpressCheckoutDetails();

        if (empty($this->_getPayment()->getStorage()->payer['payer_id'])) {

            $this->_getPayment()->getStorage()->token = null;
            $this->_redirect('/paymentpaypal/express-checkout/index');
        }

        if ($response) {
            $zoneId = Axis::single('location/zone')->getIdByCode(
                urldecode($response['SHIPTOSTATE'])
            );
            $countryId = Axis::single('location/country')
                    ->getIdByName(urldecode($response['SHIPTOCOUNTRYNAME']));

            $company = !empty($response['BUSINESS']) ?
                urldecode($response['BUSINESS']) : '';

            $delivery = $this->_getCheckout()->getDelivery();
            if (null === $delivery) {
                $delivery = array();
            } else {
                $delivery = $delivery->toArray();
            }
            $this->_getCheckout()->setDelivery(array_merge($delivery, array(
                'firstname'      => urldecode($response['FIRSTNAME']),
                'lastname'       => urldecode($response['LASTNAME']),
                'street_address' => urldecode($response['SHIPTOSTREET']),
                'suburb'         => urldecode($response['SHIPTOSTREET2']),
                'city'           => urldecode($response['SHIPTOCITY']),
                'postcode'       => urldecode($response['SHIPTOZIP']),
                'zone_id'        => $zoneId,
                'country_id'     => $countryId,
                'company'        => $company
            )));
        }
        if (!$this->_getCheckout()->getDelivery() instanceof Axis_Address) {
            $this->_redirect('/paymentpaypal/express-checkout/delivery');
        }

        if (null === $this->_getCheckout()->getShippingMethodCode()) {
            $this->_redirect('/paymentpaypal/express-checkout/shipping-method');
        }
        if (true == $this->_getPayment()->getStorage()->markflow) {
            $this->_redirect('/paymentpaypal/express-checkout/process');
        }

        $this->_forward('confirmation');
    }

    public function confirmationAction()
    {
        $checkout = $this->_getCheckout();
        $this->view->checkout = array(
            'delivery' => $checkout->getDelivery(),
            'email'    => urldecode(
                $this->_getPayment()->getStorage()->payer['payer_email']
            ),
            'products' => $checkout->getCart()->getProducts(),
            'totals'   => $checkout->getTotal()->getCollects(),
            'total'    => $checkout->getTotal()->getTotal(),
            'shipping' => $checkout->shipping()->getTitle()
        );
        $this->render();
    }

    public function deliveryAction()
    {
        $selectedAddressId = null;
        $delivery = $this->_getCheckout()->getDelivery();
        if ($delivery instanceof Axis_Address && $delivery->hasId()) {
            $selectedAddressId = $delivery->id;
        }
        $this->view->addressList = Axis::single('account/customer_address')
            ->getSortListByCustomerId(Axis::getCustomerId());

        $this->view->assign(array(
            'selectedAddressId' => $selectedAddressId,
            'addressType'       => 'delivery',
            'formAddress'       => $this->_getAddressForm()
        ));
        $this->render('delivery');
    }

    public function setShippingMethodAction()
    {
        $methodCode = $this->_getParam('method');
        // methodCode can include method type also - Pickup_Standard|Ups_Standard_WXS
        list($moduleName, $methodName) = explode('_', $methodCode);

        if (!in_array(
                $moduleName . '_' . $methodName,
                Axis_Shipping::getMethodNames()
            )) {

            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    "'%s' method not found among installed modules", $methodCode
                )
            );
            $this->_redirect('/paymentpaypal/express-checkout/shipping-method');
        }

        $method = Axis_Shipping::getMethod($methodCode);
        if (!$method instanceof Axis_Method_Shipping_Model_Abstract ||
            !$method->isEnabled() ||
            !$method->isAllowed($this->_getCheckout()->getShippingRequest()))
        {
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    'Selected shipping method in not allowed'
                )
            );
            $this->_redirect('/paymentpaypal/express-checkout/shipping-method');
        }

        $this->_getCheckout()->setShippingMethodCode($methodCode);
        $this->_redirect('/paymentpaypal/express-checkout/confirmation');
    }

    public function setDeliveryAction()
    {
        $addressId = $this->_getParam('delivery-id');
        if ($this->_checkAddress($addressId)) {
            $this->_getCheckout()->setDelivery($addressId);
        } else {
            $params = $this->_getAllParams();
            $form = Axis::single('account/Form_Address');

            if (!$form->isValid($params)) {
                $this->_helper->json->sendJson(
                    $form->getMessages(), false, false
                );
            }
            $address = array(
                 'firstname'      => $params['firstname'],
                 'lastname'       => $params['lastname'],
                 'street_address' => $params['street_address'],
                 'suburb'         => '',
                 'city'           => $params['city'],
                 'postcode'       => $params['postcode'],
                 'phone'          => $params['phone'],
                 'zone'           => Axis::single('location/zone')
                    ->find($params['zone_id'])->current()->toArray(),
                 'country'        => Axis::single('location/country')
                    ->find($params['country_id'])->current()->toArray()
            );
            $this->_getCheckout()->setDelivery($address);
        }
        if (null === $this->_getCheckout()->getShippingMethodCode()) {
            $this->_redirect('/paymentpaypal/express-checkout/shipping-method');
        }
        $this->_redirect('/paymentpaypal/express-checkout/confirmation');
    }

    public function processAction()
    {
        $checkout = $this->_getCheckout();
        $total = $checkout->getTotal()->getTotal();

        $response = $this->_getPayment()->runDoExpressCheckoutPayment();
        if (!$response) {
            $this->_redirect('/paymentpaypal/express-checkout/cancel');
        }

        if (empty($this->_getPayment()->getStorage()->payer['payer_email'])) {

            $delivery = $checkout->getDelivery();

            $this->_getPayment()->getStorage()->payer['payer_email'] =
                $delivery->firstname . ' ' . $delivery->lastname;
        }

        $checkout->setBilling(array(
            'firstname' => 'Paypal Account: ',
            'lastname' => $this->_getPayment()->getStorage()->payer['payer_email']
//            'phone' => '',
//            'fax' => '',
//            'company' => '',
//            'street_address' => '',
//            'suburb' => '',
//            'city' => '',
//            'postcode' => '',
//            'state' => '',
//            'country' => '',
//            'address_format_id' => 1,
        ));

        $checkout->getStorage()->customer_comment = $this->_getParam('comment');

        $order = Axis::single('sales/order')->createFromCheckout();

        $checkout->setOrderId($order->id);

//        $this->_getPayment()->postProcess($order);

        $this->_getPayment()->clear();

        $this->_redirect('checkout/index/success');
    }

    /**
     * customer cancel payment from paypal
     */
    public function cancelAction()
    {
        $this->_getPayment()->clear();
        $this->_redirect('/checkout/cancel');
    }

    public function editAction()
    {
        $this->_redirect(
            $this->_getPayment()->getPayPalLoginServer()
            . '?cmd=_express-checkout&useraction=continue&token='
            . $this->_getPayment()->getStorage()->token,
            array(),
            false
        );
    }
}