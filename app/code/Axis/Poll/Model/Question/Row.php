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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Poll
 * @subpackage  Axis_Poll_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Poll_Model_Question_Row extends Axis_Db_Table_Row
{
    /**
     * @see app/code/Axis/Poll/Box/Poll.php
     * @return int
     */
    public function getTotalVoteCount()
    {
        $languageId = Axis_Locale::getLanguageId();
        
        return Axis::model('poll/vote')->select('*')
            ->join('poll_answer', 'pa.id = pv.answer_id')
            ->where('pa.question_id = ?', $this->id)
            ->where('pa.language_id = ?', $languageId)
            ->count()
            ;
    }

    /**
     *
     * @return array 
     */
    public function getResults()
    {
        $languageId = Axis_Locale::getLanguageId();
        
        return Axis::model('poll/vote')->select(
                array('answer_id', 'cnt' => 'COUNT(*)')
            )
            ->join('poll_answer', 'pa.id = pv.answer_id')
            ->where('pa.question_id = ?', $this->id)
            ->where('pa.language_id = ?', $languageId)
            ->group('pv.answer_id')
            ->order('cnt')
            ->fetchAssoc()
            ;
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