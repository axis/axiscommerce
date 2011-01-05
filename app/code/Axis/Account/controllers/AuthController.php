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
class Axis_Account_AuthController extends Axis_Core_Controller_Front
{

    protected function _generatePassword()
    {
        mt_srand((double)microtime(1)*1000000);
        return md5(mt_rand());
    }

    public function init()
    {
        parent::init();
        $this->view->crumbs()->add(
            Axis::translate('account')->__('Auth'), '/account/auth'
        );
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Log in or create an account'
        );
        $this->view->meta()->setTitle($this->view->pageTitle);
        if (Axis::getCustomerId()) {
            $this->_redirect('account');
        }

        $this->render();
    }

    public function loginAction()
    {
        Axis::single('account/customer')->login(
            $this->_getParam('username'),
            $this->_getParam('password')
        );

        $this->_redirect($this->_hasSnapshot() ?
            $this->_getSnapshot() :
                $this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function logoutAction()
    {
        Axis::single('account/customer')->logout();
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function registerAction()
    {
        if (Axis::getCustomerId()) {
            $this->_redirect('account');
        }

        $this->view->pageTitle = Axis::translate('account')->__('Create an Account');
        $this->view->meta()->setTitle($this->view->pageTitle);
        $form = Axis::single('account/form_signup');

        if ($this->_request->isPost()) {
            $request = $this->_request->getPost();
            if ($form->isValid($request)) {
                $mCustomer = Axis::single('account/customer');
                $request['site_id'] = Axis::getSiteId();
                $request['is_active'] = 1;
                $result = $mCustomer->save($request);
                $mCustomer->login($request['email'], $request['password']);

                Axis::dispatch('account_customer_register_success', array(
                    'customer' => $mCustomer->find($result['id'])->current(),
                    'password' => $result['password']
                ));

                return $this->_redirect('account');
            } else {
                $form->populate($request);
            }
        }

        $this->view->formSignup = $form;
        $this->render();
    }
}