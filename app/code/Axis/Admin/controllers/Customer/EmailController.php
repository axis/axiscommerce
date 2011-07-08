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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Customer_EmailController extends Axis_Admin_Controller_Back
{
    public function sendAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getAllParams();
        $customer = Axis::model('account/customer')->select()
            ->where('email = ?', $data['email'])
            ->fetchRow();

        try {
            $mail = new Axis_Mail();
            $mail->setLocale($customer->locale);
            $configResult = $mail->setConfig(array(
                'event'   => 'default',
                'subject' => $data['subject'],
                'data'    => array(
                    'text'      => $data['message'],
                    'firstname' => $customer->firstname,
                    'lastname'  => $customer->lastname,
                    'customer'  => $customer
                ),
                'to' => $data['email']
            ));
            $mail->send();

            if ($configResult) {
                Axis::message()->addSuccess(
                    Axis::translate('core')->__('Mail was sended successfully')
                );
            }
        } catch (Zend_Mail_Transport_Exception $e) {
            Axis::message()->addError(
                Axis::translate('core')->__('Mail sending was failed.')
                . ' ' . $e->getMessage()
            );
        }
    }
}
