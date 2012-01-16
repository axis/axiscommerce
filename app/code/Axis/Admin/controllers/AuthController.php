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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_AuthController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('index');
        }
        $this->_helper->layout->disableLayout();
        if ($this->_hasParam('messages')) {
            $this->view->messages = $this->_getParam('messages');
        }
        $this->render('form-login');
    }

    public function loginAction()
    {
        $username = $this->_getParam('username');
        $password = $this->_getParam('password');
        $auth = Zend_Auth::getInstance();
        $authAdapter = new Axis_Auth_AdminAdapter($username, $password);

        $result = $auth->authenticate($authAdapter);

        if (!$result->isValid()) {
            Axis::dispatch('admin_user_login_failed', array('username' => $username));
            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
        } else {
            Zend_Session::regenerateId();
            Axis::dispatch('admin_user_login_success', array('username' => $username));
            Axis::session()->roleId = Axis::single('admin/user')->select('role_id')
                ->where('id = ?', $result->getIdentity())
                ->fetchOne();
            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
        }
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        unset(Axis::session()->roleId);
        $this->_redirect('auth');
    }
}
