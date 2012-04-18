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
class Axis_Account_AuthController extends Axis_Core_Controller_Front
{
    public function init()
    {
        parent::init();
        $this->_helper->breadcrumbs(array(
            'label'      => Axis::translate('account')->__('Auth'),
            'controller' => 'auth',
            'route'      => 'account'
        ));
    }

    public function indexAction()
    {
        if (Axis::getCustomerId()) {
            $this->_redirect('account');
        }
        $this->setTitle(Axis::translate('account')->__(
            'Log in or create an account'
        ));
        $this->render();
    }

    public function loginAction()
    {
        Axis::single('account/customer')->login(
            $this->_getParam('username'),
            $this->_getParam('password')
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            if (Axis::getCustomerId()) { // bad login method design. try..catch or return information should be used
                $this->_helper->json->sendSuccess();
            } else {
                $this->_helper->json->sendFailure();
            }
            return;
        }

        $this->_redirect($this->_hasSnapshot() ?
            $this->_getSnapshot() : $this->_getBackUrl());
    }

    public function logoutAction()
    {
        Axis::single('account/customer')->logout();
        $this->_redirect($this->_getBackUrl());
    }

    public function registerAction()
    {
        if (Axis::getCustomerId()) {
            $this->_redirect('account');
        }

        $this->setTitle(Axis::translate('account')->__('Create an Account'));

        $form = Axis::single('account/form_signup');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $model = Axis::single('account/customer');
                $data['site_id'] = Axis::getSiteId();
                $data['is_active'] = 1;
                list($row, $password) = $model->create($data);
                $row->setDetails($data);

                Axis::dispatch('account_customer_register_success', array(
                    'customer' => $row,
                    'password' => $password
                ));

                $model->login($data['email'], $password);
                return $this->_redirect('account');
            } else {
                $form->populate($data);
            }
        }

        $this->view->formSignup = $form;
        $this->render();
    }
}