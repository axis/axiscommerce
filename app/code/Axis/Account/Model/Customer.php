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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer extends Axis_Db_Table
{
    protected $_name = 'account_customer';

    protected $_rowClass = 'Axis_Account_Model_Customer_Row';

    protected $_selectClass = 'Axis_Account_Model_Customer_Select';

    protected $_dependentTables = array('Axis_Account_Model_Customer_Detail');

    /**
     *
     * @param array $data
     * @return type
     */
    public function create(array $data)
    {
        $row = $this->createRow($data);

        if (empty($row->password)) {
            $row->password = $this->generatePassword();
        }
        $password = $row->password;
        $row->password = md5($password);

        $row->modified_at = Axis_Date::now()->toSQLString();

        if (empty($row->created_at)) {
            $row->created_at = $row->modified_at;
        }

        if (empty($row->locale)) {
            $row->locale = Axis_Locale::getLocale()->toString();
        }

        $row->save();

        return array($row, $password);
    }

    /**
     * @param integer $length [optional]
     * @return string
     */
    public function generatePassword($length = 7)
    {
        $chars = '0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLength = strlen($chars);
        $password = '';
        $counter  = 0;

        while ($counter++ < $length) {
            $password .= substr($chars, rand(0, $charLength - 1), 1);
        }

        return $password;
    }

    /**
     *
     * @param string $email
     * @param string $password
     * @return Axis_Account_Model_Customer Provides fluent interface
     */
    public function login($email, $password)
    {
        if (empty($email) || empty($password)) {
            return $this;
        }

        $auth = Zend_Auth::getInstance();
        $authAdapter = new Axis_Auth_FrontAdapter($email, $password);
        $result = $auth->authenticate($authAdapter);

        if (!$result->isValid()) {
            Axis::dispatch('account_customer_login_failed', array('username' => $email));
            foreach ($result->getMessages() as $error) {
                Axis::message()->addError($error);
            }
        } else {
            Zend_Session::regenerateId();
            Axis::dispatch('account_customer_login_success', array('username' => $email));
            Axis::single('checkout/cart')->merge();
        }

        return $this;
    }

    /**
     * Clears private data
     *
     * @return Axis_Account_Model_Customer Provides fluent interface
     */
    public function logout()
    {
        Axis::dispatch('account_customer_logout_success', array('id' => Axis::getCustomerId()));
        Zend_Auth::getInstance()->clearIdentity();
        Axis::single('checkout/cart')->unsetCartId();
        Axis::single('checkout/checkout')->getStorage()->unsetAll();
        return $this;
    }

    /**
     * Checks, is user exists in database. If not - clearIdentity called
     *
     * @return void
     */
    public function checkIdentity()
    {
        if (!Axis::getCustomerId()) {
            return;
        }
        if (!$customer = Axis::getCustomer()) {
            return $this->logout();
        }
        if (!$customer->is_active) {
            Axis::message()->addNotice(Axis::translate('Axis_Account')->__(
                'Your account is not active. Please contact site administrator for more details'
            ));
            $this->logout();
        }
    }

    /**
     *
     * @param int $customerId [optional]
     * @return int|null
     */
    public function getGroupId($customerId = null)
    {
        $customerGroupId = null;

        if (!$customerId && $row = Axis::getCustomer()) {
            $customerGroupId = $row->group_id;
        } else if ($customerId && $row = $this->find($customerId)->current()) {
            $customerGroupId = $row->group_id;//parent::getGroupId()
        }

        if (null === $customerGroupId)  {
            $customerGroupId = Axis_Account_Model_Customer_Group::GROUP_GUEST_ID;
        }
        return $customerGroupId;
    }
}