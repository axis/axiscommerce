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
 * @copyright   Copyright 2008-2012 Axis
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
     * Return visitor row
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getVisitor()
    {
        if (!isset(Axis::session()->visitorId) ||
            (!$row = $this->find(Axis::session()->visitorId)->current()))
         {
            $row = $this->createRow(array(
                'session_id'  => Zend_Session::getId(),
                'customer_id' => Axis::getCustomerId() ? 
                    Axis::getCustomerId() : new Zend_Db_Expr('NULL'),
            ));
            $row->save();
            Axis::session()->visitorId = $row->id; //unset only on logout
        }
        return $row;
    }
}