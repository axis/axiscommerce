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
class Axis_Checkout_OnepageController extends Axis_Checkout_Controller_Checkout
{
    public function init()
    {
        parent::init();
        $title = Axis::translate('checkout')->__('Checkout Process');
        $this->addBreadcrumb(array(
            'label'      => Axis::translate('checkout')->__('Checkout Onepage'),
            'controller' => 'cart',
            'route'      => 'checkout'
        ));
        $this->setTitle($title, $title, false);
    }

    public function indexAction()
    {
        $this->_validateCart();

        $formSignup = Axis::model('account/form_signup');
        $formSignup->setAttrib('onsubmit', 'register(); return false;');

        $this->view->formAddress = $this->_getAddressForm();
        $this->view->formSignup = $formSignup;
        $this->render('one-page');
    }

    public function asGuestAction()
    {
        $this->_helper->layout->disableLayout();
        /**
         *  @var Zend_Session_Namespace $storage
         */
        $storage = $this->_getCheckout()->getStorage();
        $storage->asGuest = true;

        $register = (bool)$this->_getParam('register_guest', false);
        $storage->registerGuest = $register;

        return $this->_helper->json->sendSuccess();
    }

    public function addressListAction()
    {
        $this->_helper->layout->disableLayout();
        $storage = $this->_getCheckout()->getStorage();
        if (!$storage->asGuest) {
            parent::addressListAction();
            return;
        }
        $formAddress = $this->_getAddressForm();
        $formAddress->getActionBar()->removeElement('submit');
        if ('billing-address' === ($type = $this->_getParam('type'))) {
            $formAddress->setAttrib('id', 'guest-billing-form-new-address');
            if ($storage->registerGuest) {
                $formAddress->addRegisterField();
            } else {
                $formAddress->addGuestField();
            }
        } else {
            $formAddress->setAttrib('id', 'guest-delivery-form-new-address');
            $formAddress->populate(
                $this->_getCheckout()->getBilling()->toFlatArray()
            );
        }

        $formAddress->setAction(
            $this->view->href('/checkout/onepage/set-' . $type, true)
        );
        echo $formAddress->render();
    }

    public function setBillingAddressAction()
    {
        $this->_helper->layout->disableLayout();
        $checkout = $this->_getCheckout();
        $storage = $checkout->getStorage();

        if (!$storage->asGuest) {
            $addressId = $this->_getParam('billing-address-id');
            if (!$this->_checkAddress($addressId)) {
                Axis::message()->addError(
                    Axis::translate('checkout')->__(
                        'Invalid address'
                    )
                );
                return $this->_helper->json->sendFailure();
            }
            if ($this->_getParam('use_as_delivery', false)) {
                $result = $checkout->setBilling($addressId) &&
                          $checkout->setDelivery($addressId);
                return $this->_helper->json->sendJson(array('success' => $result));
            }
            return $this->_helper->json->sendJson(array(
                'success' => $checkout->setBilling($addressId)
            ));
        }
        //GUEST
        $formAddress = $this->_getAddressForm();
        if ($storage->registerGuest) {
            $formAddress->addRegisterField();
        } else {
            $formAddress->addGuestField();
        }
        //form action must be "post"
        $params = $this->_request->getPost();

        if (!$formAddress->isValid($params)) {
            return $this->_helper->json->sendRaw($formAddress->getMessages());
        }
        $address = $params;
        if ($this->_getParam('use_as_delivery', false)) {
            $result = $checkout->setBilling($address) &&
                      $checkout->setDelivery($address);
            return $this->_helper->json->sendJson(array('success' => $result));
        }
        return $this->_helper->json->sendJson(array(
            'success' => $checkout->setBilling($address)
        ));
    }

    public function setDeliveryAddressAction()
    {
        $this->_helper->layout->disableLayout();
        $checkout = $this->_getCheckout();
        $storage = $checkout->getStorage();

        if (!$storage->asGuest) {
            $addressId = $this->_getParam('delivery-address-id');
            if (!$this->_checkAddress($addressId)) {
                Axis::message()->addError(
                    Axis::translate('checkout')->__(
                        'Invalid address'
                    )
                );
                return $this->_helper->json->sendFailure();
            }
            return $this->_helper->json->sendJson(array(
                'success' => $checkout->setDelivery($addressId)
            ));
        }

        $formAddress = $this->_getAddressForm();
        //form action must be "post"
        $params = $this->_request->getPost();

        if (!$formAddress->isValid($params)) {
            return $this->_helper->json->sendRaw($formAddress->getMessages());
        }

        return $this->_helper->json->sendJson(array(
            'success' => $checkout->setDelivery($params)
        ));
    }

    public function shippingMethodAction()
    {
        $this->_helper->layout->disableLayout();
        parent::shippingMethodAction();
    }

    public function paymentMethodAction()
    {
        $this->_helper->layout->disableLayout();
        parent::paymentMethodAction();
    }

    public function setPaymentMethodAction()
    {
        $this->_helper->layout->disableLayout();
        $methodCode = $this->_getParam('method');

        if (!in_array($methodCode, Axis_Payment::getMethodNames())) {
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    "'%s' method not found among installed modules", $methodCode
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $method = Axis_Payment::getMethod($methodCode);
        if (!$method instanceof Axis_Method_Payment_Model_Abstract ||
            !$method->isEnabled() ||
            !$method->isAllowed($this->_getCheckout()->getPaymentRequest()))
        {
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    'Selected payment method is not allowed'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $this->_getCheckout()->setPaymentMethodCode($methodCode);

        return $this->_helper->json->sendJson(array(
            'success' => $this->_getCheckout()->payment()->saveData(
                $this->_getAllParams()
            )
        ));
    }

    public function setShippingMethodAction()
    {
        $this->_helper->layout->disableLayout();
        $methodCode = $this->_getParam('method');

        // methodCode can include method type also - Pickup_Standard|Ups_Standard_WXS
        list($moduleName, $methodName) = explode('_', $methodCode);

        if (!in_array($moduleName . '_' . $methodName, Axis_Shipping::getMethodNames())) {
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    "'%s' method not found among installed modules", $methodCode
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $method = Axis_Shipping::getMethod($methodCode);
        if (!$method instanceof Axis_Method_Shipping_Model_Abstract
            || !$method->isEnabled()
            || !$method->isAllowed($this->_getCheckout()->getShippingRequest()))
            {
                Axis::message()->addError(
                    Axis::translate('checkout')->__(
                        'Selected shipping method in not allowed'
                    )
                );
            return $this->_helper->json->sendFailure();
        }

        $this->_getCheckout()->setShippingMethodCode($methodCode);
        return $this->_helper->json->sendSuccess();
    }

    public function confirmationAction()
    {
        $this->_helper->layout->disableLayout();
        parent::confirmationAction();
    }
}