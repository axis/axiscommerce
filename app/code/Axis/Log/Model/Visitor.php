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
 * @package     Axis_Log
 * @subpackage  Axis_Log_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Log
 * @subpackage  Axis_Log_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
 class Axis_Log_Model_Visitor extends Axis_Db_Table
{
    protected $_name = 'log_visitor';
    protected $_selectClass = 'Axis_Log_Model_Visitor_Select';

    /**
     * Upadte or create table visitor row 
     *
     * @return  Axis_Log_Model_Visitor Fluet interface
     */
    public function updateVisitor()
    {
        $customerId = Axis::getCustomerId();
        $sessionId = Zend_Session::getId();
        $rowData = array(
            'session_id'    => $sessionId,
            'customer_id'   => $customerId ?
                $customerId : new Zend_Db_Expr('NULL'),
            // add link trace
            'last_url_id'   => Axis::single('log/url_info')->add(),
            'last_visit_at' => Axis_Date::now()->toSQLString(),
            'site_id'       => Axis::getSiteId()
        );
        $visitorId = isset(Axis::session()->visitorId) ?
            Axis::session()->visitorId : null;
        //salt
        if ((!$visitorId || !$row = $this->fetchRow('id = ' . $visitorId)) 
               || ($row->customer_id != $customerId && !$row = $this->fetchRow(
                   "session_id = '{$sessionId}' AND customer_id"
                   . ($customerId ? " = {$customerId}" : ' IS NULL'))
               )
           ) {
                $row = $this->createRow($rowData);
        } else {
            $row->setFromArray($rowData);
        }
        $row->save();
        Axis::session()->visitorId = $row->id;
        Axis::single('log/visitor_info')->updateVisitorInfo($row->id);
        Axis::single('log/url')->add($row->last_url_id, $row->id);

        return $this;
    }

    /**
     * Return visitor row
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getVisitor()
    {
        if (!isset(Axis::session()->visitorId)) {
            $this->updateVisitor();
        }
        return $this->find(Axis::session()->visitorId)->current();
    }

    /**
     *
     * @param  mixed $where
     * @return array
     */
    public function getCountList($where = null)
    {
        $select = $this->getAdapter()->select();
        $select->from(
                array('o' =>  $this->_prefix . 'log_visitor'),
                array('last_visit_at', 'hit'=> 'COUNT(DISTINCT session_id)')
            )
           ->group('last_visit_at')
           ->order('last_visit_at');
                    
        if (is_string($where) && $where) {
            $select->where($where);
        } elseif (is_array($where)) {
            foreach ($where as $condition) {
                if ($condition)
                    $select->where($condition);
            }
        }
        return $this->getAdapter()->fetchPairs($select->__toString());
    }
}