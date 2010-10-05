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
 * @package     Axis_Auth
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Auth
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Auth_FrontAdapter implements Zend_Auth_Adapter_Interface
{
    /**
     *
     * @var string
     */
    private $_username;

    /**
     *
     * @var string
     */
    private $_password;

    /**
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     *
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $row = Axis::single('account/customer')->fetchRow(
            Axis::db()->quoteInto('email = ?', $this->_username)
        );
        $messages = array();
        if (!$row) {
            $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $messages[] = Axis::translate('account')->__(
                'Username or password is incorrect'
            );
            return new Zend_Auth_Result($code, null, $messages);
        } elseif ($row->password != md5($this->_password)) {
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $messages[] = Axis::translate('account')->__(
                'Username or password is incorrect'
            );
        } elseif (!$row->is_active) {
            $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $messages[] = Axis::translate('account')->__(
                'Your account is not active. Please contact site administrator for more details'
            );
        } else {
            $code = Zend_Auth_Result::SUCCESS;
        }

        return new Zend_Auth_Result($code, $row->id, $messages);
    }
}