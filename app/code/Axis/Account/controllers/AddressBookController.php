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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_AddressBookController extends Axis_Account_Controller_Abstract
{
    /**
     * @var int
     */
    private $_customerId;

    public function init()
    {
        parent::init();
        $this->_customerId = Axis::getCustomerId();
        $this->_helper->breadcrumbs(array(
            'label'      => Axis::translate('account')->__('Address Book'),
            'controller' => 'address-book',
            'route'      => 'account'

        ));
    }

    public function indexAction()
    {
        $this->setTitle(Axis::translate('account')->__('Address Book'), null, false);

        $this->view->defaultBillingAddresId = false;
        $this->view->defaultShippingAddresId = false;
        if ($customer = Axis::getCustomer()) {
            $this->view->defaultBillingAddresId = $customer->default_billing_address_id;
            $this->view->defaultShippingAddresId = $customer->default_shipping_address_id;
        }

        $this->view->addressList = Axis::single('account/customer_address')
            ->getSortListByCustomerId($this->_customerId);
        $this->render();
    }

    public function saveAction()
    {
        $this->setTitle(Axis::translate('account')->__('Saving address'));

        $params = $this->_getAllParams();
        $form = Axis::model('account/form_address');

        if ($form->isValid($params)) {
            $params['customer_id'] = $this->_customerId;
            Axis::getCustomer()->setAddress($params);

            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_helper->json->sendRaw(true);
                return;
            }
            $this->_redirect('account/address-book');
        } else {
            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_helper->json->sendRaw($form->getMessages());
                return;
            }
            $form->populate($params);
        }

        $this->view->form = $form;
        $this->render('form-address');
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        Axis::single('account/customer_address')->delete(array(
            $this->db->quoteInto('id = ?', $id),
            $this->db->quoteInto('customer_id = ?', $this->_customerId)
        ));
        $this->_redirect('account/address-book');
    }

    public function newAction()
    {
        $this->setTitle(
            Axis::translate('account')->__(
                'Add new address'
        ));

        $customer = Axis::getCustomer();
        $this->view->form = Axis::model('account/form_address');
        $this->view->form->populate(array_merge(
            Axis::model('account/customer_address')->getDefaultValues(),
            array(
                'firstname' => $customer->firstname,
                'lastname'  => $customer->lastname
            )
        ));
        $this->render('form-address');
    }

    public function editAction()
    {
        $this->setTitle(
            Axis::translate('account')->__(
                'Edit address'
        ));

        $addressId = $this->_getParam('id');
        $address = Axis::single('account/customer_address')->select()
            ->where('id = ?', $addressId)
            ->where('customer_id = ?', $this->_customerId)
            ->fetchRow();

        if (!$address instanceof  Axis_Db_Table_Row) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Address not found'
            ));
            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->_helper->json->sendFailure();
            }
            $this->_redirect('account/address-book');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->_helper->json->sendSuccess(array(
                'data' => $address->toArray()
            ));
        }

        $form = Axis::model('account/form_address');

        $customer = Axis::getCustomer();

        $form->populate($address->toArray());

        if ($customer->default_shipping_address_id == $addressId) {
            $form->getElement('default_shipping')
                ->setOptions(array('value' => 1));
        }
        if ($customer->default_billing_address_id == $addressId) {
            $form->getElement('default_billing')
                ->setOptions(array('value' => 1));
        }

        $this->view->form = $form;
        $this->render('form-address');
    }
}