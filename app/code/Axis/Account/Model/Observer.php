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
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Observer
{
    /**
     * Send notification emails to registered cutomer, store owner
     *
     * @param array $data
     * Array(
     *  'customer' => Axis_Account_Model_Customer_Row
     *  'password' => string
     * )
     * @return void
     */
    public function notifyCustomerRegistration($data)
    {
        $mail = new Axis_Mail();
        $isC = $mail->setConfig(array(
            'event' => 'account_new-customer',
            'subject' => Axis::translate('account')->__(
                "Welcome, %s %s",
                $data['customer']->firstname,
                $data['customer']->lastname
            ),
            'data' => array(
                'firstname' => $data['customer']->firstname,
                'lastname'  => $data['customer']->lastname,
                'customer'  => array(
                    'email'     => $data['customer']->email,
                    'password'  => $data['password']
                )
            ),
            'to' => $data['customer']->email
        ));
        if ($isC) {
            $mail->send();
        }

        $mailNotice = new Axis_Mail();
        $isC = $mailNotice->setConfig(array(
            'event'   => 'account_new-owner',
            'subject' => 'Created new account',
            'data'    => array('customer' => $data['customer']->toArray()),
            'to'      => Axis_Collect_MailBoxes::getName(
                Axis::config()->sales->order->email
            )
        ));
        if ($isC) {
            $mailNotice->send(null, false);
        }
    }
}