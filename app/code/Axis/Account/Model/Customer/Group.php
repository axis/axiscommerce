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
class Axis_Account_Model_Customer_Group extends Axis_Db_Table
{
    protected $_name = 'account_customer_group';
    protected $_primary = 'id';

    /**
     *
     * @param array $params
     * @return array
     */
    public function getList($params = array())
    {
        $select = $this->getAdapter()->select();
        
        $select->from($this->_prefix . 'account_customer_group');
        
        if (isset($params['sort'])&& (isset($params['dir']))) {
            $select->order($params['sort'] . ' ' . $params['dir']);
        } 
        
        if (isset($params['where'])) {
          $select->where($params['where']);
        }
        
        return $this->getAdapter()->fetchAll($select->__toString());
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->getAdapter()->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->_prefix . 'account_customer_group'
        );
    }

    /**
     *
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        $existsCustomerGroups = $this->getAdapter()->fetchCol(
            "SELECT id FROM " . $this->_prefix . 'account_customer_group'
        );

        $return = true;
        foreach ($data as $groupId => $values) {
            $row = array(
                'name' => $values['name'],
                'description' => $values['description']
            );
            
            if (in_array($groupId, $existsCustomerGroups)) {
                $return = $return && $this->update(
                    $row,
                    $this->getAdapter()->quoteInto(
                        "id = ? AND name <> 'Guest'", $groupId
                    )
                );
            } else {
                $return = $return && $this->insert($row);
            }
        }
        
        Axis::message()->addSuccess(
            Axis::translate('account')->__(
                'Group was saved successfully'
            )
        );
        return $return;
    }
}