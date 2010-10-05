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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * UserModel 
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_UserForgotPassword extends Axis_Db_Table
{
    /**
     * The default table name 
     */
    protected $_name = 'admin_user_forgotpassword';
    
    public function save(array $data)
    {
        if (empty($data['created_at'])) {
            $data['created_at'] = Axis_Date::now()->toSQLString();
        }
        if (count($this->find($data['user_id'])))
            return $this->update(
                $data, 
                $this->getAdapter()->quoteInto('user_id = ?', $data['user_id'])
            );
        return $this->insert($data);
    }   
    
    public function getUserNameByHash($hash)
    {
        $select = $this->getAdapter()->select();
        $select->from(array('au' => $this->_prefix . 'admin_user'), 'username')
               ->joinLeft(array('aufp' => $this->_prefix . 'admin_user_forgotpassword'),
                   'au.id = aufp.user_id', array())
               ->where("aufp.hash = '{$hash}'");
        return $this->getAdapter()->fetchOne($select->__toString());
    }
    
    public function isValid($hash, $username = null) 
    {
         $select = $this->getAdapter()->select();
         $date = Axis_Date::now()->addDay(-1)->toSQLString();
         $select->from(array('aufp' => $this->_prefix . 'admin_user_forgotpassword'), 'COUNT(*)')
                ->joinLeft( array('au' => $this->_prefix . 'admin_user'),
                    'au.id = aufp.user_id', array())
                ->where("aufp.hash = ?", $hash)
                ->where("aufp.created_at > ?", $date);
         if ($username) {
            $select->where("au.username =  ?", $username);
         }
         $count = $this->getAdapter()->fetchOne($select->__toString());
         return $count ? true : false;
    }
}
