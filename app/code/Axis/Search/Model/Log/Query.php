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
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Search
 * @subpackage  Axis_Search_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Search_Model_Log_Query extends Axis_Db_Table
{
    protected $_name = 'search_log_query';

    /**
     *
     * @param string $query
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function getQuery($query)
    {
    	$row = $this->fetchRow($this->getAdapter()->quoteInto('`query` = ?', $query));
    	if ($row) {
            return $row;
        }
        $row = $this->createRow(array('query' => $query));
        $row->save();
    	return $row;
    }
}