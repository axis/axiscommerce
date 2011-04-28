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
class Axis_Log_Model_Url extends Axis_Db_Table
{
    protected $_name = 'log_url';
    protected $_selectClass = 'Axis_Log_Model_Url_Select';
    
    /**
     *
     * @param  mixed $where
     * @return array
     */
    public function getCountList($where = null, $period = 'day')
    {
        switch (strtolower($period)) {
            case 'hour' :
                $period = 16;
                break;
            case 'week' :
            case 'month':
                $period = 10;
                break;
            case 'year' :
                $period = 7;
                break;
            case 'day':
            default:
                $period = 13;
        }
        $select = Axis::single('log/url')
            ->select(array(
                'd' => "LEFT(visit_at, {$period})", 
                'hit'=> 'COUNT(DISTINCT visitor_id)'
            ))
            ->group('d')
            ->order('d');
        
        if (is_string($where) && $where) {
            $select->where($where);
        } elseif (is_array($where)) {
            foreach ($where as $condition) {
                if ($condition)
                    $select->where($condition);
            }
        }

        $datatimePattern = "0000-00-00 00:00:00";
        $dataset = array();
        foreach ($select->fetchPairs() as $key => $value) {
            if (strlen($datatimePattern) > strlen($key))  {
                $key .= substr($datatimePattern, strlen($key)); 
            }
            $dataset[$key] = $value;
        }
        return $dataset;
    }
}