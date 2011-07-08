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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Poll
 * @subpackage  Axis_Poll_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Poll_Model_Answer extends Axis_Db_Table
{
    protected $_name = 'poll_answer';

    /**
     *
     * @param int $questionId
     * @param int $languageId
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return array Collection of rows, each in a format by the fetch mode.
     */
    public function getAnswers(
        $languageId = false,
        $questionId = false ,
        $order = null,
        $count = null,
        $offset = null)
    {
        $select = $this->select(array('id', 'question_id', 'answer', 'language_id'))
            ->joinLeft('poll_question', 'pa.question_id = pq.id');
        
        if (null !== $count) {
            $select->limit($count, $offset);
        }
        if (null !== $order) {
            $select->order($order);
        }
        
        if (false !== $questionId) {
            $select->where('pq.id = ?', $questionId);
        }
        if (false !== $languageId) {
            $select->where('pa.language_id = ?', $languageId);
        }
        
        return $select->query()->fetchAll();
    }
      
    /*
     * @TODO rename
     */
    public function getAttitude($questionId, $answerId)
    {
        return $this->select()
            ->where('question_id = ?', $questionId)
            ->where('id = ?', $answerId)
            ->count();
    }
}