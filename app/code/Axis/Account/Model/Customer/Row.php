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
        $select = $this->getAdapter()->select();
        $select->from(array('cd' => $this->_prefix . 'account_customer_detail'), array('customer_valueset_value_id' , 'data'))
               ->join(array('cf' => $this->_prefix . 'account_customer_field'),
               'cd.customer_field_id = cf.id')
               ->where('cd.customer_id = ' . intval($this->id))
               ->where('cf.name =  ?', $fieldName);
        $rows = $this->getAdapter()->fetchAll($select->__toString());

        if (isset($rows[0]) && !empty($rows[0]['data'])){
            return $rows[0]['data'];
        } elseif (isset($rows[0]) && !empty($rows[0]['customer_valueset_value_id'])) {
            $valuesetIds = array();
            foreach ($rows as $row) {
                $valuesetIds[] = $row['customer_valueset_value_id'];
            }

            $select = $this->getAdapter()->select();
            $select->from(array('cvvl' => $this->_prefix . 'account_customer_valueset_value_label'), 'label')
                   ->where('cvvl.valueset_value_id IN  (?)', $valuesetIds)
                   ->where('cvvl.language_id =  ?', $languageId);

            return $this->getAdapter()->fetchCol($select->__toString());
        }
        return false;
    }

    /**
     * Update, inserts or delete customer address.
     * To delete address add key 'remove' to address data
     *
     * @param array $address
     * @return int
     */
    public function setAddress(array $address)
    {
        $mAddress = Axis::single('account/customer_address');

        $address['customer_id'] = $this->id;
        if (empty($address['zone_id'])) {
            $address['zone_id'] = new Zend_Db_Expr('NULL');
        }
        if (!isset($address['remove'])) {
            $address['remove'] = 0;
        }

        if (!isset($address['id'])
            || !$row = $mAddress->find($address['id'])->current()) {

            if ($address['remove']) {
                return 0;
            }

            unset($address['id']);
            $row = $mAddress->createRow($address);
            $addressId = $row->save();

            // if this is a first address - make it default for shipping and billing
            $isFirstAddress = (bool) $mAddress->select()
                ->where('customer_id = ?', $this->id)
                ->count('id') == 1;

            if ($isFirstAddress) {
                $this->default_billing_address_id  = $row->id;
                $this->default_shipping_address_id = $row->id;
            }
        } elseif ($address['remove']) {
            return $row->delete();
        } else {
            $row->setFromArray($address);
            $addressId = $row->save();

            if (isset($address['default_billing']) && $address['default_billing']) {
                $this->default_billing_address_id  = $row->id;
            }
            if (isset($address['default_shipping']) && $address['default_shipping']) {
                $this->default_shipping_address_id = $row->id;
            }
        }

        $this->save();

        return $addressId;
    }
}