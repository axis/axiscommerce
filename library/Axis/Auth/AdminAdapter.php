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
class Axis_Auth_AdminAdapter implements Zend_Auth_Adapter_Interface
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
        $row = Axis::single('admin/user')->select()
            ->where('username = ?', $this->_username)
            ->fetchRow3();
        
        if (!$row) {
            $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Such user does not exists'
            ));
            return new Zend_Auth_Result($code, null);
        }
        
        if ($row->password != md5($this->_password)) {
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Wrong password'
            ));
            return new Zend_Auth_Result($code, null);
        }
        
        $code = Zend_Auth_Result::SUCCESS;
        $row->lastlogin = Axis_Date::now()->toSQLString();
        $row->save();

        return new Zend_Auth_Result($code, $row->id);
    }
}