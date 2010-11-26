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
class Axis_Account_Model_Customer extends Axis_Db_Table
{
    protected $_name = 'account_customer';
    protected $_rowClass = 'Axis_Account_Model_Customer_Row';
    protected $_selectClass = 'Axis_Account_Model_Customer_Select';
    protected $_dependentTables = array('Axis_Account_Model_Customer_Detail');

    /**
     * Update or insert customer row
     *
     * @param array $data
     * @return array|false
     */
    public function save(array $data)
    {
        if (!isset($data['id']) || !$row = $this->find($data['id'])->current()) {
            unset($data['id']);
            $row = $this->createRow();
            $row->created_at = Axis_Date::now()->toSQLString();
            if (!isset($data['password']) || empty($data['password'])) {
                $data['password'] = $this->generatePassword();
            }
        }
        $password = '';
        if (isset($data['password']) && !empty($data['password'])) {
            $password = $data['password'];
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }

        $row->setFromArray($data);
        $row->modified_at = Axis_Date::now()->toSQLString();

        if (!$row->save()) {
            return false;
        }

        Axis::single('account/customer_detail')->save($row->id, $data);

        return array(
            'id'        => $row->id,
            'password'  => $password
        );
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
            Axis::dispatch('account_customer_login_success', array('username' => $email));
            Axis::single('checkout/cart')->merge();
            Axis::single('checkout/checkout')->getStorage()->asGuest = null;
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
     *
     * @param array $params
     * @return array
     */
    public function getList($params = array())
    {
        $select = $this->select('*')
            ->calcFoundRows()
            ->distinct();

        if (isset($params['getSitesName']) && (bool) $params['getSitesName']) {
            $select->joinLeft(
                'core_site', 'cs.id = ac.site_id', array('site_name' => 'name')
            );
        }
        if (isset($params['customer_id'])) {
            $select->where('ac.id = ?', $params['customer_id']);
        }
        $isCustomerEmail = (int) isset($params['customer_email']);
        $isFirstName     = (int) isset($params['firstname']);
        $isLastName      = (int) isset($params['lastname']);
        $whereMethod = 'where';
        if (($isCustomerEmail + $isFirstName + $isLastName) > 1) {
            $whereMethod = 'orWhere';
        }
        if (isset($params['customer_email'])) {
            $select->$whereMethod("ac.email LIKE '%{$params['customer_email']}%'");
        }

        if (isset($params['firstname'])) {
            $select->$whereMethod("ac.firstname LIKE '%{$params['firstname']}%'");
        }
        if (isset($params['lastname'])) {
            $select->$whereMethod("ac.lastname LIKE '%{$params['lastname']}%'");
        }

        if (!empty($params['sort'])) {
            $select->order($params['sort'] . ' ' . $params['dir']);
        }
        if (!empty($params['limit'])) {
            $select->limit($params['limit'], $params['start']);
        }

        return array(
            'accounts' => $select->fetchAll(),
            'count'    => $select->count()
        );
    }

    /**
     *
     * @param string|array|Zend_Db-Select $where
     * @return array
     */
    public function getCountList($where = null)
    {
        $select = $this->getAdapter()->select();
        $select->from(
                array('o' => $this->_prefix . 'account_customer'),
                array("created_at" ,'COUNT(*) as hit')
            )
           ->group('created_at')
           ->order('created_at');
        if (is_string($where) && $where) {
            $select->where($where);
        } elseif (is_array($where)) {
            foreach ($where as $condition) {
                if ($condition) {
                    $select->where($condition);
                }
            }
        }
        return $this->getAdapter()->fetchPairs($select->__toString());
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
        if (!$customer = $this->find(Axis::getCustomerId())->current()) {
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
     * @param int $customerId
     * @return int|null
     */
    public function getGroupId($customerId)
    {
        $customerGroupId = null;

        if ($customerId && $row = $this->find($customerId)->current()) {
            $customerGroupId = $row->group_id;//parent::getGroupId()
        }

        if (null === $customerGroupId)  {
            $customerGroupId =  Axis::single('account/customer_group')
                ->getIdByName('Guest');
        }
        return $customerGroupId;
    }
}