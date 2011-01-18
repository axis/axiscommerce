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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_ForgotController extends Axis_Admin_Controller_Back
{
    public function init()
    {
        $this->_disableAcl  = true;
        $this->_disableAuth = true;
        parent::init();
    }

    protected function _generatePassword()
    {
        mt_srand((double)microtime(1)*1000000);
        return md5(mt_rand());
    }

    public function registerAction()
    {
        $this->layout->disableLayout();
        $email = $this->_getParam('email', null);
        if (!$this->_request->isPost()) {
            $this->render();
            return;
        }
        if (empty($email)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Email address is required'
            ));
            $this->render();
            return;
        }
        if (Axis::single('admin/user')->hasEmail($email)) {
            $hash = $this->_generatePassword();
            $link = $this->view->href('forgot') . '?hash=' . $hash;

            try {
                $mail = new Axis_Mail();
                $configResult = $mail->setConfig(array(
                    'event'   => 'forgot_password',
                    'subject' => Axis::translate('admin')->__(
                        'Forgot Your Backend Password'
                    ),
                    'data'    => array(
                        'link' => $link,
                        'firstname' => Axis::single('admin/user')
                            ->getFirstnameByEmail($email),
                        'lastname' => Axis::single('admin/user')
                            ->getLastnameByEmail($email)
                    ),
                    'to' => $email
                ));
                $mail->send();

                if ($configResult) {
                    Axis::single('admin/UserForgotPassword')->save(array(
                        'hash' => $hash,
                        'user_id' => Axis::single('admin/user')->getIdByEmail($email)
                    ));
                    Axis::message()->addSuccess(
                        Axis::translate('admin')->__('See your mailbox to proceed')
                    );
                }
            } catch (Zend_Mail_Transport_Exception $e) {
                Axis::message()->addError(
                    Axis::translate('core')->__('Mail sending was failed.')
                );
            }
        } else {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Email address was not found in our records'
                )
            );
        }
        $this->render();
    }

    public function indexAction()
    {
        $this->layout->disableLayout();
        $hash = $this->_getParam('hash', null);
        $this->view->hash = $hash;
        if (!$username = Axis::single('admin/UserForgotPassword')
            ->getUserNameByHash($hash)) {

            $this->_redirect('/');
            return;

        }
        $this->view->username = $username;
        $this->render();
    }

    public function confirmAction()
    {
        $data = $this->_getAllParams();
        if (empty($data['password'])) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Password is required'
            ));
            $this->_forward(
                'index', null, null, array('hash' => $data['hash'])
            );
            return;
        }
        if ($data['password'] != $data['password_confirm']) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Password confirmation does not match'
            ));
            $this->_forward(
                'index', null, null, array('hash' => $data['hash'])
            );
            return;
        }
        if (!Axis::single('admin/userForgotPassword')
                ->isValid($data['hash'], $data['username'])) {

            Axis::message()->addError(
               Axis::translate('admin')->__(
                   "Invalid hash recieved. Please fill forgot form again"
            ));
            $this->_redirect('forgot/register');
            return;
        }

        Axis::single('admin/user')->update(array(
            'password' => md5($data['password'])),
            $this->db->quoteInto('username = ?', $data['username'])
        );
        Axis::single('admin/userForgotPassword')->delete(
            $this->db->quoteInto('hash = ?', $data['hash'])
        );
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                "Password successfully changed"
        ));
        $this->_redirect('auth');
    }

}
