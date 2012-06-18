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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Auth
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Auth_Adapter_Frontend implements Zend_Auth_Adapter_Interface
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
        $rowset = Axis::single('account/customer')->select()
            ->where('email = ?', $this->_username)
            ->where('site_id = ?', Axis::getSiteId())
            ->fetchRowset();
        if (!$rowset->count() && Axis::config('account/main/crossSiteLogin')) {
            $rowset = Axis::single('account/customer')->select()
                ->where('email = ?', $this->_username)
                ->fetchRowset();
        }
        $messages = array();
        $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
        $id = null;
        foreach ($rowset as $row) {
            if ($row->password != md5($this->_password)) {
                $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;

            } elseif (!$row->is_active) {
                $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            } else {
                $id = $row->id;
                $code = Zend_Auth_Result::SUCCESS;
                break;
            }
        }

        switch ($code) {
            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                $messages[] = Axis::translate('account')->__(
                    'Username or password is incorrect'
                );
                break;
            case Zend_Auth_Result::FAILURE_UNCATEGORIZED:
                $messages[] = Axis::translate('account')->__(
                    'Your account is not active. Please contact site administrator for more details'
                );
                break;

            default:
                break;
        }

        return new Zend_Auth_Result($code, $id, $messages);
    }
}