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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Poll
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Poll_Model_Question_Row extends Axis_Db_Table_Row
{
    /**
     * @use app/code/Axis/Poll/Box/Poll.php
     * @return int
     */
	public function getTotalVoteCount()
	{
		return $this->getAdapter()->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->_prefix . 'poll_vote as  pv ' .  
		    'INNER JOIN ' . $this->_prefix . 'poll_answer pa ON pa.id = pv.answer_id ' .
		    'WHERE pa.question_id = ? AND pa.language_id = ?',
            array($this->id, Axis_Locale::getLanguageId())
        );
	}
	
    public function getResults()
    {
        $result = $this->getAdapter()->fetchAssoc(
            'SELECT pv.answer_id, COUNT(*) AS cnt ' .
            'FROM ' . $this->_prefix . 'poll_vote pv ' .
            'INNER JOIN ' . $this->_prefix . 'poll_answer pa ON pa.id = pv.answer_id ' .
            'WHERE pa.question_id = ? AND pa.language_id = ?' .
            'GROUP BY pv.answer_id ' .
            'ORDER BY cnt' ,
            array($this->id, Axis_Locale::getLanguageId())
        );
        return $result;
    }

    /**
     *
     * @return bool
     */
    public function isMultiQuestion()
    {
        return (1 == $this->type);
    }
}