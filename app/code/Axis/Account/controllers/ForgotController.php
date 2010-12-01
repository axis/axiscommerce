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
class Axis_Account_ForgotController extends Axis_Core_Controller_Front
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
            Axis::translate('account')->__('Forgot'), '/account/auth/forgot'
        );
    }
    
	public function registerAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Forgot password'
        );
        
        $username = $this->_getParam('username', null);
        if (empty($username)) {
             $this->render();
             return;
        }
        $customerId = Axis::single('account/customer')->getIdByEmail($username);

        if ($customerId
            && $customer = Axis::single('account/customer')->find($customerId)->current()
            && Axis::getSiteId() === $customer->site_id
            ) {

            $hash = $this->_generatePassword();
            $link = $this->view->href('/account/forgot', true)
                  . '?hash=' . $hash;
            
            $mail = new Axis_Mail();               
            $mail->setConfig(array(
                'event'   => 'forgot_password',
                'subject' => 'Forgot Your Password',
                'data'    => array(
                    'link' => $link,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname
                ),
                'to'   => $username,
                'from' => array('name' => Axis::config()->core->store->owner)
            ));
            if (@$mail->send()) {
                Axis::single('account/customer_forgotPassword')->save(array(
                    'hash' => $hash,
                    'customer_id' => $customerId
                ));
                Axis::message()->addSuccess(Axis::translate('core')->__(
                    'Message was sended to you. Check your mailbox'
                ));
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
        };

        $this->view->pageTitle = Axis::translate('account')->__(
            'Retrieve Forgotten Password'
        );
        $this->view->hash = $hash;
        $this->render();
    }
    
    public function confirmAction()
    {
        $params = $this->_getAllParams();
        if (empty($params['password']) || empty($params['password_confirm'])) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Password is the required field'
            ));
            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
            return;
        }
        if ($params['password'] != $params['password_confirm']) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Password confirmation does not match'
            ));
            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
            return;
        }
        $modelForgotPass = Axis::single('account/customer_forgotPassword');
        if (!$modelForgotPass->isValid($params['hash'])) {
            Axis::message()->addError(Axis::translate('account')->__(
                'Recieved hash is corrupted.'
            ));
            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
            return;
            
        } 
        $isUpdatePassword = Axis::single('account/customer')->update(array(
            'password' => md5($params['password'])),
            $this->db->quoteInto('email = ?',
                $modelForgotPass->getEmailByHash($params['hash'])
            )
        );
        if ($isUpdatePassword) {
            $modelForgotPass->delete(
                $this->db->quoteInto('hash = ?', $params['hash'])
            );
        }
        
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }
}