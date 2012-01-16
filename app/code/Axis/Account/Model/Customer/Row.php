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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_Row extends Axis_Db_Table_Row
{
    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->select()
            ->from('account_customer_detail')
            ->where('acd.customer_id = ?', $this->id)
            ->fetchAll();
    }

    /**
     * @param name $fieldName
     * @param int $languageId
     * @return mixed
     */
    public function getExtraField($fieldName, $languageId = null)
    {

        if (!$languageId) {
            $languageId = Axis_Locale::getLanguageId();
        }
        $row = Axis::model('account/customer_detail')->select('*')
            ->join('account_customer_field', 'acd.customer_field_id = acf.id')
            ->where('acd.customer_id = ? ', $this->id)
            ->where('acf.name =  ?', $fieldName)
            ->fetchRow();

        if (!$row) {
            return false;
        }

        if ($row->data){
            return $row->data;
        }
        if ($row->customer_valueset_value_id) {
            return Axis::model('account/customer_valueSet_value_label')
                ->select('label')
                ->where('valueset_value_id = ?', $row->customer_valueset_value_id)
                ->where('language_id = ?', $languageId)
                ->fetchCol();
        }
        return false;
    }

    /**
     * Update, inserts or delete customer address.
     * To delete address add key 'remove' to address data
     *
     * @param array $data
     * @return int
     */
    public function setAddress(array $data)
    {
        $model = Axis::single('account/customer_address');

        $row = $isFirst = false;
        if (!empty($data['id'])) {
            $row = $model->find($data['id'])->current();
        }
        if (!$row) {
            unset($data['id']);
            $row = $model->createRow();
            $isFirst = ($model->select()
                ->where('customer_id = ?', $this->id)
                ->count('id') == 0);
        }
        $row->setFromArray($data);
        $row->customer_id = $this->id;
        if (empty($row->zone_id) || isset($data['state'])) {
            $row->zone_id = new Zend_Db_Expr('NULL');
        } else {
            $row->state = '';
        }
        $row->save();

        if ((isset($data['default_billing']) && $data['default_billing'])
            || $isFirst) {

            $this->default_billing_address_id  = $row->id;
        }
        if ((isset($data['default_shipping']) && $data['default_shipping'])
            || $isFirst) {

            $this->default_shipping_address_id = $row->id;
        }

        $this->save();

        return $row->id;
    }

    /**
     *
     * @param type $data
     */
    public function setDetails($data)
    {
        $modelDetail = Axis::model('account/customer_detail');

        $modelDetail->delete(
            Axis::db()->quoteInto('customer_id = ?', $this->id)
        );

        $fields = Axis::single('account/customer_field')->select()
            ->fetchAssoc();

        $multiFields = Axis_Account_Model_Customer_Field::$fieldMulti;

        foreach ($data as $id => $value) {
            if (0 !== strpos($id, 'field_') || empty($value)) {
                continue;
            }
            list(, $id) = explode('_', $id);


            $_row = array(
                'customer_id'       => $this->id,
                'customer_field_id' => $id
            );
            $isMultiField = in_array($fields[$id]['field_type'], $multiFields);

            if ($isMultiField && is_string($value) && strpos($value, ',')) {
                $value = explode(',', $value);
            }

            if ($isMultiField && is_array($value)) {

               foreach ($value as $_value) {
                    $row = $modelDetail->createRow($_row);
                    $row->customer_valueset_value_id = $_value;
                    $row->save();
                }

            } elseif($isMultiField) {
                $row = $modelDetail->createRow($_row);
                $row->customer_valueset_value_id = $value;
                $row->save();
            } else {
                $row = $modelDetail->createRow($_row);
                $row->data = $value;
                $row->save();
            }
        }
    }
}