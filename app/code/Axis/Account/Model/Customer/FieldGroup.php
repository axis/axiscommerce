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
    public function save($data)
    {
        $db = $this->getAdapter();
        
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return false;
        }
            
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        $label = Axis::single('account/Customer_FieldGroup_Label');
        $groupName = preg_replace(
            array("/[^a-z0-9\s+]/", "/\s+/"),
            array('', '_'),
            strtolower($data['groupName'])
        );

        $row = array(
            'name' => $groupName,
            'sort_order' => $data['sortOrder'],
            'is_active' => $data['isActive']
        ); 
        
        if ($data['groupId'] != 'null') {
            $this->update($row, $db->quoteInto('id = ?', $data['groupId']));
            foreach ($languageIds as $languageId) {
                $label->update(
                   array('group_label' => $data['groupTitle' . $languageId]),
                   array(
                       $db->quoteInto(
                           'customer_field_group_id = ?', $data['groupId']
                       ),
                       'language_id = ' . $languageId
                   )
                );
            }
        } else {
            $groupId = $this->insert($row);
            foreach ($languageIds as $languageId) {
                $label->insert(array(
                    'customer_field_group_id' => $groupId,
                    'language_id' => $languageId,
                    'group_label' => $data['groupTitle' . $languageId]
                ));
            }
            Axis::message()->addSuccess(
                Axis::translate('account')->__(
                    'Group was saved successfully'
                )
            );
            return $groupId;
        }
        Axis::message()->addSuccess(
            Axis::translate('account')->__(
                'Group was saved successfully'
            )
        );
        return $data['groupId'];
    }
}