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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Db
 * @subpackage  Axis_Db_Table
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Db_Table_Filter extends Zend_Db_Table_Abstract
{
    /**
     * set filters
     *
     * @static
     * @param  Zend_Db_Select
     * @param array Filters
     * @return  mixed
     */
    public static function set(Zend_Db_Select &$select, $filters = array())
    {
        $db = Axis::db();
        if (!sizeof($filters))
            return;
        $tableShortName = key($select->getPart(Zend_Db_Select::FROM));
        //error_log(print_r($select->getPart(Zend_Db_Select::COLUMNS)));
        foreach ($filters as $filter) {
            switch ($filter['data']['type']) {
                case 'numeric':
                case 'date':
                    $condition = $filter['data']['comparison'] == 'eq' ? '=' :
                                 ($filter['data']['comparison'] == 'lt' ? '<' : '>');
                    $select->where("$tableShortName.$filter[field] $condition ?", $filter['data']['value']);
                    break;

                case 'list':
                    $select->where($db->quoteInto(
                        "$tableShortName.$filter[field] IN (?)",
                        explode(',', $filter['data']['value'])
                    ));
                    break;

                default:
                    $select->where("$tableShortName.$filter[field] LIKE ?", $filter['data']['value'] . "%");
                    break;
            }
        }
        return $select;
    }
}