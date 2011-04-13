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
class Axis_Checkout_WizardController extends Axis_Checkout_Controller_Checkout
{
    public function init()
    {
        parent::init();
        $this->view->pageTitle = Axis::translate('checkout')->__('Checkout Process');
        $this->view->meta()->setTitle($this->view->pageTitle);
        $this->addBreadcrumb(array(
            'label'      => Axis::translate('checkout')->__('Checkout Wizard'),
            'controller' => 'wizard',
            'route'      => 'checkout'
        ));
        if (!Axis::getCustomerId()
            && !$this->_getCheckout()->getStorage()->asGuest
            && !in_array(
                $this->getRequest()->getActionName(),
                array('index', 'login', 'as-guest')
            )) {

            $this->_forward('login');
        }
    }
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        parent::_validateCart();
        $this->_redirect('/checkout/wizard/login');
    }

    public function loginAction()
    {
        if (Axis::getCustomerId()) {
            $this->_redirect('/checkout/wizard/billing-address');
        }
        $this->setTitle(Axis::translate('checkout')->__('Login'));

        $formSignup = Axis::single('account/form_signup');
        $formSignup->setAttrib('onsubmit', 'register(); return false;');

        $this->view->formAddress = $this->_getAddressForm();
        $this->view->formSignup = $formSignup;
        $this->view->nextPage = '/checkout/wizard/billing-address';
        $this->render();
    }

    public function asGuestAction()
    {
        $this->_helper->layout->disableLayout();
        /**
         *  @var Zend_Session_Namespace $storage
         */
        $storage = $this->_getCheckout()->getStorage();
        $storage->asGuest = true;

        $storage->registerGuest = (bool)$this->_getParam('register_guest', false);
        $this->_redirect('/checkout/wizard/billing-address');
    }

    public function billingAddressAction()
    {
        $storage = $this->_getCheckout()->getStorage();
        if (!$storage->asGuest) {
            $this->getRequest()
                ->setActionName('address-list')
                ->setParam('type', 'billing-address')
            ;
            $this->addressListAction();
            return;
        }
        $formAddress = $this->_getAddressForm();
        $formAddress->getActionBar()
            ->getElement('submit')
            ->setLabel(Axis::translate('checkout')->__('Continue'));

        if ($storage->registerGuest) {
            $formAddress->addRegisterField();
        } else {
            $formAddress->addGuestField();
        }

        $formAddress->setAction(
            $this->view->href('/checkout/wizard/set-billing-address')
        );
        if (($billing = $this->_getCheckout()->getBilling())
            && $formAddress->isValid($billing->toFlatArray())) {

            $formAddress->populate($billing->toFlatArray());
        }
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
                        'Incorrect address recieved'
                    )
                );
                $this->_redirect('/checkout/wizard/billing-address');
                die;
            }
            if ($this->_getParam('use_as_delivery', false)) {
                $result = $checkout->setBilling($addressId) &&
                          $checkout->setDelivery($addressId);

                $this->_redirect('/checkout/wizard/shipping-method');
                return ;
            }
            $checkout->setBilling($addressId);
            $this->_redirect('/checkout/wizard/delivery-address');
        }
        $formAddress = $this->_getAddressForm();
        if ($storage->registerGuest) {
            $formAddress->addRegisterField();
        } else {
            $formAddress->addGuestField();
        }
        $params = $this->_request->getPost();

        $checkout->setBilling($params);
        if (!$formAddress->isValid($params)) {

            $this->_redirect('/checkout/wizard/billing-address');
            return;
        }
        if ((bool)$this->_getParam('use_as_delivery', false)) {
            $checkout->setDelivery($params);
            $this->_redirect('/checkout/wizard/shipping-method');
            return ;
        }

        $this->_redirect('/checkout/wizard/delivery-address');
    }

    public function deliveryAddressAction()
    {
        $storage = $this->_getCheckout()->getStorage();
        if (!$storage->asGuest) {
            $this->getRequest()
                ->setActionName('address-list')
                ->setParam('type', 'delivery-address')
            ;
            $this->addressListAction();
            return;
        }
        $formAddress = $this->_getAddressForm();
        $formAddress->getActionBar()
            ->getElement('submit')
            ->setLabel(Axis::translate('checkout')->__('Continue'));

        $billing = $this->_getCheckout()->getBilling();
        $formAddress->populate($billing->toFlatArray());
        $formAddress->setAction(
            $this->view->href('/checkout/wizard/set-delivery-address')
        );
        if (($delivery = $this->_getCheckout()->getDelivery())
            && $formAddress->isValid($delivery->toFlatArray())) {

            $formAddress->populate($delivery->toFlatArray());
        }
        echo $formAddress->render();
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
                        'Incorrect address recieved'
                    )
                );
                $this->_redirect('/checkout/wizard/delivery-address');
                return;
            }
            $checkout->setDelivery($addressId);
            $this->_redirect('/checkout/wizard/shipping-method');
            return;
        }
        $formAddress = $this->_getAddressForm();
        $request = $this->_request->getPost();
        $checkout->setDelivery($request);
        $this->_redirect('/checkout/wizard/shipping-method');
        return;
    }

    public function shippingMethodAction()
    {
        $this->setTitle(
            Axis::translate('checkout')->__(
                'Shipping Method'
        ));
        parent::shippingMethodAction();
    }

    public function paymentMethodAction()
    {
        $this->setTitle(
            Axis::translate('checkout')->__(
                'Payment Method'
        ));
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
            $this->_redirect('/checkout/wizard/payment-method');
            return;
        }

        $method = Axis_Payment::getMethod($methodCode);
        if (!$method instanceof Axis_Method_Payment_Model_Abstract ||
            !$method->isEnabled() ||
            !$method->isAllowed($this->_getCheckout()->getPaymentRequest()))
        {
            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    'Selected payment method in not allowed'
                )
            );
            $this->_redirect('/checkout/wizard/payment-method');
            return;
        }

        $this->_getCheckout()->setPaymentMethodCode($methodCode);
        $this->_redirect('/checkout/wizard/confirmation');
    }

    public function setShippingMethodAction()
    {
        $this->_helper->layout->disableLayout();
        $methodCode = $this->_getParam('method');

        // methodCode can include method type also - Pickup_Standard|Ups_Standard_WXS
        list($moduleName, $methodName) = explode('_', $methodCode);

        if (!in_array($moduleName . '_' . $methodName, Axis_Shipping::getMethodNames())) {
            Axis::message()->addError(Axis::translate('checkout')->__(
                "'%s' method not found among installed modules", $methodCode
            ));
            $this->_redirect('/checkout/wizard/shipping-method');
            return;
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
            $this->_redirect('/checkout/wizard/shipping-method');
            return;
        }

        $this->_getCheckout()->setShippingMethodCode($methodCode);
        $this->_redirect('/checkout/wizard/payment-method');
    }

    public function confirmationAction()
    {
        $this->setTitle(
            Axis::translate('checkout')->__(
                'Confirm your order'
        ));
        parent::confirmationAction();
    }


    public function processAction()
    {
        $this->_forward('process', 'index', 'Axis_Checkout');
    }
}