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
 * @package     Axis_Sales
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Status extends Axis_Db_Table 
{
    protected $_name = "sales_order_status";
    protected $_rowClass = 'Axis_Sales_Model_Order_Status_Row';
    protected $_primary = array('id');
    
    /**
     * Retrieve an array of order statuses
     * @param int parentId  
     * @return array
     */
    public function getList($statusId = null)
    {
        if (null !== $statusId) {
            $childrens = Axis::single('sales/order_status_relation')->getChildrens($statusId);
        }
        
        $select = $this->getAdapter()->select()
            ->from(array('sos' => $this->_prefix . 'sales_order_status'))
            ->joinLeft(array('sost' => $this->_prefix . 'sales_order_status_text'),
                'sos.id = sost.status_id',
                array('language_id', 'status_name'));
        if (null !== $statusId) {
            if (!count($childrens)) {
                return array();
            }
            $select->where($this->getAdapter()->quoteInto("sos.id IN (?)", $childrens));
        }
        return $this->getAdapter()->fetchAll($select);
    }
    
    /**
     * Update existing or create new order status
     * 
     * @param array $data
     * @return void
     */
    public function batchSave($data)
    {
        $languages = array_keys(Axis_Collect_Language::collect());
        
        foreach ($data as $id => $row) {
            if (!$this->getSystem(intval($row['id']))) {
                $this->update(
                    array('name' => $row['name']),
                    'id = ' . intval($row['id'])
                );
            }
            foreach ($languages as $langId) {
                if (!isset($row['status_name_' . $langId]))
                    continue;

                if (!$record = Axis::single('sales/order_status_text')->find($row['id'], $langId)->current()) {
                    $record = Axis::single('sales/order_status_text')->createRow(array(
                        'status_id' => intval($row['id']), 
                        'language_id' => intval($langId),
                        'status_name' => $row['status_name_' . $langId]
                    ));
                } else {
                    $record->setFromArray(array(
                        'status_name' => $row['status_name_' . $langId]
                    ));
                }
                $record->save();
            }
        }
        
        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                'Status was saved successfully'
        ));
    }
    
    /**
     * 
     * @return 
     * @param int $id
     * @param string $name 
     * @param array|int|string $parent
     * @param array|int|string $children
     * @param array of string $translates ( 1 => 'pending', 2 => 'jxsredfyyz')
     * @return mixed false|$this Provides fluent interface
     */
    public function save(
            $id,
            $name,
            $parent = array(),
            $children = array(),
            $translates = array())
    {
        $system = 0;
        if ($this->getSystem($id)) {
            $name = $this->getName($id);
            $system = 1;
            Axis::message()->addNotice(
                Axis::translate('sales')->__(
                    "Relation no change. Order status %s is SYSTEM ", $name
            ));
        }
        $this->update(
            array('name' => $name, 'system' => $system), 
            $this->getAdapter()->quoteInto('id = ?', $id)
        );
        
        if (!is_array($parent)) {
            if (is_string($parent)) {
                $parent = array($this->getIdByName($parent));
            } elseif ($parent) {
                $parent = array($parent);
            } else {
                $parent = array();
            }
        }
        if (!is_array($children)) {
            if (is_string($children)) {
                $children = array($this->getIdByName($children));
            } else {
                $children = array($children);
            }
        }
        if (!$system) {
            Axis::single('sales/order_status_relation')->delete(
                $this->getAdapter()->quoteInto('from_status = ? OR to_status = ? ', array($id, $id))
            );
            
            foreach ($parent as $from) {
                Axis::single('sales/order_status_relation')->add($from, $id);
            }
            
            foreach ($children as $to) {
                Axis::single('sales/order_status_relation')->add($id, intval($to));
            }
        }
        
        Axis::single('sales/order_status_text')->delete(
            $this->getAdapter()->quoteInto('status_id = ?', $id)
        );
        foreach (array_keys(Axis_Collect_Language::collect()) as $langId) {
            if (!isset($translates[$langId]))
                continue;
            
            Axis::single('sales/order_status_text')->insert(array(
                'status_id' => $id,
                'language_id' => $langId,
                'status_name' => $translates[$langId]
            ));
        }
        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                "Order status  %s upload", $name
        ));
           
    }
    
    /**
     * 
     * @param string $name
     * @param array|int|string $parent
     * @param array|int|string $children
     * @param array of string $translates ( 1 => 'pending', 2 => 'jxsredfyyz')
     * @return mixed false|$this Provides fluent interface
     */
    public function add($name, $parent = array(), $children = array(), $translates = array())
    {
        if ($this->getIdByName($name)) {
            Axis::message()->addError(
                Axis::translate('sales')->__(
                    'Order status "%s" already exist', $name
            ));
            return false; 
        }
        if (!is_array($parent)) {
            if (is_string($parent)) {
                $parent = array($this->getIdByName($parent));
            } elseif ($parent) {
                $parent = array($parent);
            } else {
                $parent = array();
            }
        }
        if (!is_array($children)) {
            if (is_string($children)) {
                $children = array($this->getIdByName($children));
            } else {
                $children = array($children);
            }
        }
        
        $id = $this->insert(array(
            'name' => $name,
            'system' => 0
        ));
        $id = intval($id);
        foreach ($parent as $from) {
            Axis::single('sales/order_status_relation')->add($from, $id);
        }
        
        foreach ($children as $to) {
            Axis::single('sales/order_status_relation')->add($id, intval($to));
        }
        
        foreach (array_keys(Axis_Collect_Language::collect()) as $langId) {
            if (!isset($translates[$langId]))
                continue;
            
            Axis::single('sales/order_status_text')->insert(array(
                'status_id' => $id,
                'language_id' => $langId,
                'status_name' => $translates[$langId]
            ));
        }
        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                "New order status create : %s", $name
        ));
        
        return $this;
    }
}