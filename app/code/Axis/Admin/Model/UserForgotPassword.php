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
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * UserModel 
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
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
        $row = $this->getRow($data);
        if (empty($row->created_at)) {
            $row->created_at = Axis_Date::now()->toSQLString();
        }
        $row->save();
        return $row;
    }   
    
    /**
     *
     * @param string $hash
     * @return string 
     */
    public function getUserNameByHash($hash)
    {
        return Axis::model('admin/user')->select('username')
            ->joinLeft('admin_user_forgotpassword',
                'au.id = auf.user_id'
            )
            ->where('auf.hash = ?', $hash)
            ->fetchOne()
            ;
    }
    
    public function isValid($hash, $username = null) 
    {
         $date = Axis_Date::now()->addDay(-1)->toSQLString();
        
         $select = $this->select('*')
             ->joinLeft('admin_user', 'au.id = auf.user_id')
             ->where('auf.hash = ?', $hash)
             ->where('auf.created_at > ?', $date);
             ;
         if ($username) {
            $select->where('au.username = ?', $username);
         }    
         return (bool) $select->count();
    }
}
