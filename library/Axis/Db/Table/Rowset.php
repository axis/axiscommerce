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
 * @package     Axis_Db
 * @subpackage  Axis_Db_Table
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Db
 * @subpackage  Axis_Db_Table
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Db_Table_Rowset extends Zend_Db_Table_Rowset
{
    /**
     * Returns the table object, or null if this is disconnected rowset
     *
     * @return Axis_Db_Table_Abstract
     */
    public function getTable()
    {
        $table = $this->_table;
        if (null === $table && !empty($this->_tableClass)) {
            $tableClass = $this->_tableClass;
            $tableClass = strtolower($tableClass);
            $tableClass = str_replace('axis_', '', $tableClass);
            $tableClass = str_replace('_model_', '/', $tableClass);
            //$this->setTable(Axis::single($tableClass));
            $table = Axis::single($tableClass);
        }
        return $table;
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Zend_Db_Table_Row_Abstract current element from the collection
     */
    public function current()
    {
        if (false === $this->valid()) {
            return null;
        }

        // do we already have a row object for this position?
        if (empty($this->_rows[$this->_pointer])) {
            $this->_rows[$this->_pointer] = new $this->_rowClass(
                array(
                    'table'    => $this->getTable(),//<-- modified here
                    'data'     => $this->_data[$this->_pointer],
                    'stored'   => $this->_stored,
                    'readOnly' => $this->_readOnly
                )
            );
        }

        // return the row object
        return $this->_rows[$this->_pointer];
    }
//
//    /**
//     * @return Axis_Db_Table_Rowset
//     */
//    public function cache()
//    {
//        $args = func_num_args() ? serialize(func_get_args()) : '';
//        return Axis::single('Axis_Cache_Frontend_Query')->setInstance(
//            $this, $args
//        );
//    }
}