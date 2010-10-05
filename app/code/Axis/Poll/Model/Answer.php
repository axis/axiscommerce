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
class Axis_Poll_Model_Answer extends Axis_Db_Table
{
    protected $_name = 'poll_answer';

    /**
     * @param int $id
     * @param int $languageId
     * @param int $questionId
     * @param string $answer
     * @return int 
     */
    public function save($id, $languageId, $questionId, $answer)
    {
        $row = $this->fetchRow(array(
            'id = ' . $id,
            'language_id = ' . $languageId,
            'question_id = ' . $questionId
        ));
        
        if (!$row instanceof Axis_Db_Table_Row) {
            $rowData = array(
                'language_id' => $languageId,
                'question_id' => $questionId,
                'answer'      => $answer
            );
            if ($id > 0) {
                $rowData['id'] = $id;
            }
            $row = $this->createRow($rowData);
        } else {
            $row->answer = $answer;
        }
        return $row->save();
    }
    
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
        $select = $this->getAdapter()->select();
        $select->from(
            array('a' => $this->_prefix . 'poll_answer'),
            array('id', 'question_id', 'answer', 'language_id')
        );
        
        if (null !== $count) {
            $select->limit($count, $offset);
        }
        if (null !== $order) {
            $select->order($order);
        }
        $select->joinLeft(array('q' => $this->_prefix . 'poll_question'),
                          'a.question_id = q.id',
                          array());
	        
	    if (false !== $questionId) {
	        $select->where('q.id = ?', $questionId);
        }
	    if (false !== $languageId) {
	        $select->where('a.language_id = ? ', $languageId);
        }
        
        return $select->query()->fetchAll();
    }
    
    public function getIdsByQuestionId($questionId)
    {
        return $this->getAdapter()->fetchCol(
            "SELECT id FROM " . $this->_prefix . 'poll_answer' . " WHERE question_id = ? ", $questionId
        );
    }
    
    /*
     * @TODO rename
     */
    public function getAttitude($questionId, $answerId)
    {
        return $this->getAdapter()->fetchOne(
            "SELECT COUNT(id) FROM " . $this->_prefix . 'poll_answer' . " WHERE question_id = ? AND id = ?",
            array($questionId, $answerId)
        );
    }
}