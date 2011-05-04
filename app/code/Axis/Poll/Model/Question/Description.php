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
class Axis_Poll_Model_Question_Description extends Axis_Db_Table
{
    protected $_name = 'poll_question_description';

    /**
     *
     * @param int $questionId
     * @param int $languageId
     * @param string $question
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save($questionId, $languageId, $question)
    {
        return $this->getRow(array(
            'question_id' => $questionId,
            'language_id' => $languageId,
            'question'    => $question
        ))->save();
    }
}