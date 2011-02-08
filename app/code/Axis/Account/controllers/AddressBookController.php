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
class Axis_Account_AddressBookController extends Axis_Account_Controller_Account
{
    /**
     * @var int
     */
    private $_customerId;

    public function init()
    {
        parent::init();
        $this->_customerId = Axis::getCustomerId();
        $this->view->crumbs()->add(
            Axis::translate('account')->__('Address Book'),
            '/account/address-book'
        );
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Address Book'
        );
        $this->view->meta()->setTitle(
            Axis::translate('account')->__(
                'Address Book'
        ));
        $this->view->defaultBillingAddresId = false;
        $this->view->defaultShippingAddresId = false;
        if ($customer = Axis::single('account/customer')->find(Axis::getCustomerId())->current()) {
            $this->view->defaultBillingAddresId = $customer->default_billing_address_id;
            $this->view->defaultShippingAddresId = $customer->default_shipping_address_id;
        }

        $this->view->addressList = Axis::single('account/customer_address')
            ->getSortListByCustomerId($this->_customerId);
        $this->render();
    }

    public function saveAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Saving address'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $params = $this->_getAllParams();
        $form = Axis::model('account/Form_Address');

        if ($form->isValid($params)) {
            $params['customer_id'] = $this->_customerId;
            Axis::single('account/customer')
                ->find($params['customer_id'])
                ->current()
                ->setAddress($params);

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
        $this->view->pageTitle = Axis::translate('account')->__(
            'Add new address'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $form = Axis::single('account/Form_Address');
        $form->addUseAsDefaultAddressCheckbox();
        $this->view->form = $form;
        $this->render('form-address');
    }

    public function editAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Edit address'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        $addressId = $this->_getParam('id');

        $row = Axis::single('account/customer_address')->fetchRow(array(
            $this->db->quoteInto('id = ?', $addressId),
            $this->db->quoteInto('customer_id = ?', $this->_customerId)
        ));

        if (!$row instanceof  Axis_Db_Table_Row) {
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
                'data' => $row->toArray()
            ));
        }

        $form = Axis::model('account/Form_Address');
        $form->addUseAsDefaultAddressCheckbox();

        $customer = Axis::single('account/customer')
            ->find($this->_customerId)->current();

        $form->populate($row->toArray());

        if ($customer->default_shipping_address_id == $addressId) {
            $form->getDisplayGroup('address')
                ->getRow('default_shipping')
                ->getElement('default_shipping')
                ->setOptions(array('value' => 1));
        }
        if ($customer->default_billing_address_id == $addressId) {
            $form->getDisplayGroup('address')
                ->getRow('default_billing')
                ->getElement('default_billing')
                ->setOptions(array('value' => 1));
        }

        $this->view->form = $form;
        $this->render('form-address');
    }
}