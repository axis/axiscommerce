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
class Axis_Account_Model_Customer_FieldGroup extends Axis_Db_Table
{
    protected $_name = 'account_customer_fieldgroup';

    /**
     *
     * @param int $languageId
     * @return array
     */
    public function getGroups($languageId)
    {
        return Axis::model('account/Customer_FieldGroup')->select('*')
            ->joinLeft('account_customer_fieldgroup_label',
                'acfl.customer_field_group_id = acf.id',
                array('title' => 'group_label')
            )
            ->where('acfl.language_id = ?', $languageId)
            ->order('sort_order')
            ->fetchAssoc();
    }

    /**
     *
     * @param int $groupId
     * @return array
     */
    public function getCurrentGroup($groupId)
    {
        return $this->select('*')
            ->join('account_customer_fieldgroup_label', 
                'acfl.customer_field_group_id = acf.id',
                array('group_label', 'language_id')
            )
            ->where('acf.id = ?', $groupId)
            ->fetchAll()
            ;
    }

    /**
     *
     * @param array $fieldGroupIds
     * @param int $languageId
     * @return array
     */
    public function getCustomGroups($fieldGroupIds, $languageId = null)
    {
        if (!is_array($fieldGroupIds)) {
            $fieldGroupIds = array($fieldGroupIds);
        }
        
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        return $this->select(array('id', 'sort_order', 'name')) //acf
            ->join(
                'account_customer_fieldgroup_label', 
                'acfl.customer_field_group_id = acf.id',
                'group_label'
            )
            ->where('acf.id IN(?)', $fieldGroupIds)
            ->where('acfl.language_id = ?', $languageId)
            ->fetchAll()
            ;
    }

    /**
     *
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        $row->save();
        return $row;
    }
}