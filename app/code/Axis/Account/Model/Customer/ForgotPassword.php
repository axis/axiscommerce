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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_ForgotPassword extends Axis_Db_Table
{
    protected $_name = 'account_customer_forgotpassword';

    /**
     *
     * @return string 
     */
    public function generatePassword()
    {
        mt_srand((double)microtime(1)*1000000);
        return md5(mt_rand());
    }

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
        $row = $this->getRow($data);
        $row->save();
        return $row;
    }

    /**
     *
     * @param string $hash
     * @param string $email
     * @return bool
     */
    public function isValid($hash, $email = null) 
    {
        $date = Axis_Date::now()->addDay(-1)->toSQLString();
        $select = $this->select('*')
            ->joinLeft('account_customer', 'ac.id = acf.customer_id')
            ->where('acf.hash = ?', $hash)
            ->where('ac.site_id = ?', Axis::getSiteId())
            ->where('acf.created_at > ?', $date);
        if (null !== $email) {
            $select->where('ac.email = ?', $email);
        }
        return (bool) $select->count();    
    }

    /**
     *
     * @param string $hash
     * @return string
     */
    public function getEmailByHash($hash)
    {
        return Axis::single('account/customer')->select('email')
            ->joinLeft('account_customer_forgotpassword',
                   'ac.id = acf.customer_id'
            )
            ->where('acf.hash = ?', $hash)
            ->fetchOne()
            ;
    }
}