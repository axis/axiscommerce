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
class Axis_Account_Model_Customer_ValueSet_Value extends Axis_Db_Table 
{
    protected $_name = 'account_customer_valueset_value';
    protected $_dependentTables = array(
        'Axis_Account_Model_Customer_ValueSet_Value_Label'
    );

    /**
     *
     * @param int $valuesetId
     * @return array
     */
    public function getValues($valuesetId)
    {
        $list = array();
        
        $i = 0;
        $rowset = $this->fetchAll('customer_valueset_id = ' . $valuesetId);
        foreach ($rowset as $row) {
            $list[$i] = $row->toArray();
            $labels = $row->findDependentRowset(
                'Axis_Account_Model_Customer_ValueSet_Value_Label'
            );
            foreach ($labels as $label) {
                $list[$i]['label' . $label->language_id] = $label->label;
            }
            ++$i;
        }
        return $list;
    }

    /**
     *
     * @param array $data
     * @param int $valueset
     * @return void
     */
    public function save($data, $valueset)
    {
        $label = Axis::single('account/Customer_ValueSet_Value_Label');
        $langIds = array_keys(Axis_Collect_Language::collect());
        foreach ($data as $id => $values) {
            $row = array(
                'customer_valueset_id'  => $valueset,
                'sort_order'            => $values['sort_order'],
                'is_active'             => $values['is_active'] ? 1 : 0,
            );
            
            if (!isset($values['new'])) {
                $this->update($row, $this->getAdapter()->quoteInto('id = ?', $id));
                //labels update at n_customer_valueset_value_label
                foreach ($langIds as $langId) {
                    if (!$record = $label->find($id, $langId)->current()) {
                        $record = $label->createRow(array(
                            'valueset_value_id' => $id,
                            'language_id' => $langId,
                            'label' => $values['label' . $langId]
                        ));
                    } else {
                        $record->setFromArray(array(
                            'label' => $values['label' . $langId]
                        ));
                    }
                    $record->save();
                }
            } else {
                $id = $this->insert($row);
                foreach ($langIds as $langId) {
                    $label->insert(array(
                        'valueset_value_id' => $id,
                        'language_id' => $langId,
                        'label' => $values['label' . $langId]
                    ));
                }
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
    }

    /**
     *
     * @param int $valueSetId
     * @param int $languageId
     * @return array
     */
    public function getCustomValues($valueSetId, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        $query = "SELECT cvv.id, cvvl.`label` FROM " . $this->_prefix . 'account_customer_valueset_value' . " cvv
            INNER JOIN " . $this->_prefix . "account_customer_valueset_value_label cvvl ON cvvl.valueset_value_id = cvv.id
            WHERE cvv.is_active = 1 AND cvv.customer_valueset_id = ? AND language_id = ? 
            ORDER BY cvv.sort_order";
        return $this->getAdapter()->fetchPairs($query, array(
            $valueSetId, $languageId
        ));
    }
}