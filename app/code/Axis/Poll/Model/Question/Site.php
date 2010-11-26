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
 * @package     Axis_Poll
 * @subpackage  Axis_Poll_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Poll
 * @subpackage  Axis_Poll_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Poll_Model_Question_Site extends Axis_Db_Table
{
    protected $_name = 'poll_question_site';
    
    public function getSitesNamesAssigns()
    {
    	$query = 'SELECT qts.question_id, s.id, s.name FROM ' . $this->_prefix . 'poll_question_site' . ' AS qts' .
            ' LEFT JOIN ' . $this->_prefix . 'core_site AS s ON s.id = qts.site_id';
        $rows = $this->getAdapter()->fetchAll($query);
        $assigns = array();
        foreach ($rows as $row) {
        	$assigns[$row['question_id']][$row['id']] = $row['name'];
        }
        return $assigns;
    }
    
    public function getSitesIds($questionId)
    {
        return $this->getAdapter()->fetchCol(
            'SELECT site_id FROM ' . $this->_prefix . 'poll_question_site' . ' WHERE question_id = ?',
            $questionId
        );
    }
}