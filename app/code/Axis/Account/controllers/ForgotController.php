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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_ForgotController extends Axis_Core_Controller_Front
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

    public function registerAction()
    {
        $this->setTitle(
            Axis::translate('account')->__(
                'Forgot password'
        ));

        $username = $this->_getParam('username', null);
        if (empty($username)) {
             $this->render();
             return;
        }
        $customerId = Axis::single('account/customer')->getIdByEmail($username);

        if ($customerId
            && ($customer = Axis::single('account/customer')->find($customerId)->current())
            && Axis::getSiteId() === $customer->site_id) {

            $modelForgotPassword = Axis::model('account/customer_forgotPassword');
            $hash = $modelForgotPassword->generatePassword();
            $link = $this->view->href('account/forgot', true) . '?hash=' . $hash;

            try {
                $mail = new Axis_Mail();
                $configResult = $mail->setConfig(array(
                    'event'   => 'forgot_password',
                    'subject' => Axis::translate('account')->__('Forgotten Password'),
                    'data'    => array(
                        'link'      => $link,
                        'firstname' => $customer->firstname,
                        'lastname'  => $customer->lastname
                    ),
                    'to' => $username
                ));

                if ($configResult) {
                    $mail->send();

                    $modelForgotPassword->save(array(
                        'hash'        => $hash,
                        'customer_id' => $customerId
                    ));
                    Axis::message()->addSuccess(Axis::translate('core')->__(
                        'Message was sended to you. Check your mailbox'
                    ));
                }
            } catch (Zend_Mail_Transport_Exception $e) {
                Axis::message()->addError(
                    Axis::translate('core')->__('Mail sending was failed.')
                );
            }
        } else {
            Axis::message()->addError(Axis::translate('account')->__(
                "'%s' was not found in database", $username
            ));
        }
        $this->render();
    }

    public function indexAction()
    {
        if (!$hash = $this->_getParam('hash', null)) {
            $this->_redirect('account/forgot/register');
        }

        $this->setTitle(
            Axis::translate('account')->__(
                'Retrieve Forgotten Password'
        ));
        $this->view->hash = $hash;
        $this->render();
    }

    public function confirmAction()
    {
        $noError = true; 
        $params  = $this->_getAllParams();
        
        if (empty($params['password']) || empty($params['password_confirm'])) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Password is the required field'
            ));
            $noError = false;
        }
        if ($params['password'] != $params['password_confirm']) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Password confirmation does not match'
            ));
            $noError = false;
        }
        $modelForgotPass = Axis::single('account/customer_forgotPassword');
        if (!$modelForgotPass->isValid($params['hash'])) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Recieved hash is corrupted.'
            ));
            $noError = false;
        }
        
        if ($noError) {
            
            $email = $modelForgotPass->getEmailByHash($params['hash']);
            $row = Axis::single('account/customer')->select()
                ->where('email = ?', $email)
                ->fetchRow();
            $row->password = md5($params['password']);
            $row->save();

            $modelForgotPass->delete(
                $this->db->quoteInto('hash = ?', $params['hash'])
            );
        }
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }
}