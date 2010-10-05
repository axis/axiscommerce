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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Select_Grid extends Axis_Db_Table_Select
{
    //@todo not work for joined column
    /**
     *
     * @param array $filters
     * @return Axis_Admin_Model_Select_Grid 
     */
    public function addGridFilters($filters = array())
    {
        if (!is_array($filters) || empty($filters)) {
            return $this;
        }
        $defaultTable = key($this->getPart(Zend_Db_Select::FROM));
        
        foreach ($filters as $filter) {
            if (isset($filter['table'])) {
                $table = $filter['table'];
            } else {
                $table = $defaultTable;
            }

            $field = $filter['field'];
            
            $value = $filter['data']['value'];
            
            switch ($filter['data']['type']) {
                case 'numeric':
                case 'date':
                   $cond = $filter['data']['comparison'] == 'eq' ? '=' :
                        ($filter['data']['comparison'] == 'noteq' ? '<>' :
                        ($filter['data']['comparison'] == 'lt' ? '<' : '>'));
                
                    $this->where("{$table}.{$field} {$cond} ?", $value);
                    break;
                case 'list':
                    $value = explode(',', $value);
                    $this->where("{$table}.{$field} IN (?)", $value);
                    break;
                default:
                    $value .= '%';
                    $this->where("{$table}.{$field} LIKE ?", $value);
                    break;
            }
        }
        return $this;
    }
}