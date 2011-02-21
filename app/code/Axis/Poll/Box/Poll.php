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
 * @subpackage  Axis_Poll_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Poll
 * @subpackage  Axis_Poll_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Poll_Box_Poll extends Axis_Account_Box_Abstract
{
    protected $_title = 'Poll';
    protected $_class = 'box-poll';
    protected $_url = 'poll';

    private $_questionId;
    private $_showResult = false;

    public function initData()
    {
        $modelQuestion = Axis::single('poll/question');
        if ($this->hasQuestionId()) {
            $this->_questionId = $this->getQuestionId();
            $this->setQuestionId(null);
        } else {
            $this->_questionId = $modelQuestion->getOneNotVotedQuestionIdByCookie();
        }
        if (!$this->_questionId) {
            return false;
        }
        $question = $modelQuestion->getQuestionWithAnswers(
            $this->_questionId,
            Axis_Locale::getLanguageId()
        );
        if (!$question) {
            return false;
        }
        $results = array();
        $totalVoteCount = 0;

        $this->_showResult = (bool) $this->getShowResult();
        $this->setShowResult(null);
        if ($this->_showResult) {
            $questionRow = Axis::single('poll/question')
                ->find($this->_questionId)
                ->current();
            $results = $questionRow->getResults();
            $totalVoteCount = $questionRow->getTotalVoteCount();
        }
        $this->updateData(array(
            'question'    => $question,
            'answers'     => $question['answers'],
            'results'     => $results,
            'status'      => $this->_showResult,
            'total_count' => $totalVoteCount
        ));
    }

    public function hasContent()
    {
        return (bool)$this->_questionId;
    }
}