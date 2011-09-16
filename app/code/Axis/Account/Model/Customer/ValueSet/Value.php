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
        $rowset = $this->fetchAll(
            'customer_valueset_id = ' . $valuesetId,
            'id DESC'
        );
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
     * @param int $valueSetId
     * @param int $languageId
     * @return array
     */
    public function getCustomValues($valueSetId, $languageId = null)
    {
        if (null === $languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }

        return $this->select('id')
            ->join('account_customer_valueset_value_label',
                'acvvl.valueset_value_id = acvv.id',
                'label'
            )
            ->where('acvv.is_active = 1')
            ->where('acvv.customer_valueset_id = ?', $valueSetId)
            ->where('acvvl.language_id = ?', $languageId)
            ->order('acvv.sort_order')
            ->fetchPairs();
    }
}