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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_ForgotPassword extends Axis_Db_Table
{
    protected $_name = 'account_customer_forgotpassword';

    /**
     *
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        if (empty($data['created_at'])) {
            $data['created_at'] = Axis_Date::now()->toSQLString();
        }
        if (count($this->find($data['customer_id']))) {
            $where = $this->getAdapter()->quoteInto(
                'customer_id = ?', $data['customer_id']
            );
            return $this->update($data, $where);
        }
        return $this->insert($data);
    }

    /**
     *
     * @param string $hash
     * @param string $email
     * @return bool
     */
    public function isValid($hash, $email = null) 
    {
         $select = $this->getAdapter()->select();
         $date = Axis_Date::now()->addDay(-1)->toSQLString();
         $select->from(array('cfp' => $this->_prefix . 'account_customer_forgotpassword'), 'COUNT(*)')
                ->joinLeft( array('c' => $this->_prefix . 'account_customer'),
                    'c.id = cfp.customer_id', array())
                ->where('cfp.hash = ?', $hash)
                ->where('c.site_id = ?', Axis::getSiteId())
                ->where('cfp.created_at > ?', $date);
         if ($email) {
            $select->where('c.email = ?', $email);
         }
         $count = $this->getAdapter()->fetchOne($select->__toString());
         return $count ? true : false;
    }

    /**
     *
     * @param string $hash
     * @return string
     */
    public function getEmailByHash($hash)
    {
        $select = $this->getAdapter()->select();
        $select->from(array('c' => $this->_prefix . 'account_customer'), 'email')
               ->joinLeft(array('cfp' => $this->_prefix . 'account_customer_forgotpassword'),
                   'c.id = cfp.customer_id', array())
               ->where("cfp.hash = '{$hash}'");
        return $this->getAdapter()->fetchOne($select->__toString());
    }
}