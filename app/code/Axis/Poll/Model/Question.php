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
class Axis_Poll_Model_Question extends Axis_Db_Table
{
    protected $_name = 'poll_question';
    protected $_rowClass = 'Axis_Poll_Model_Question_Row';

    /**
     *
     * @uses box Poll
     * @return int
     */
    public function getOneNotVotedQuestionIdByCookie()
    {
        $select = $this->select('id')
            ->joinInner('poll_question_site', 'pq.id = pqs.question_id')
            ->where('pq.status = 1')
            ->where('pqs.site_id = ?', Axis::getSiteId())
            ->order(array('pq.created_at DESC', 'pq.id DESC'))
            ->limit(1);

        if ($cookieIds = Axis::single('poll/vote')->getQuestionIdsFromCookie()) {
            $select->where('pq.id NOT IN (?)', $cookieIds);
        }
        return (int) $select->fetchOne();
    }

    /**
     *
     * @uses  admin/poll_index/get-question
     * @param int $questionId
     * @return array
     */
    public function getQuestionById($questionId)
    {
        return $this->select(array('id', 'type', 'status'))
            ->joinLeft('poll_question_description', 
                'pqd.question_id = pq.id', 
                array('languageId' => 'language_id', 'question')
            )
            ->where('pq.id = ?', $questionId)
            ->fetchAll()
            ;
    }

    /**
     *
     * @uses in box poll
     * @param array|int $questionId
     * @param int|bool $languageId
     * @return array
     */
    public function getQuestionWithAnswers($questionId, $languageId)
    {
        $select = $this->select(array('id', 'type'))
            ->joinLeft( 'poll_answer',
                'pa.question_id = pq.id',
                array('answer_id' => 'id' , 'answer')
            )->joinLeft('poll_question_description',
                'pqd.question_id = pq.id',
                array('question', 'language_id')
            )
            ->where('pq.id = ?', $questionId)
            ->where('pa.language_id = ?', $languageId)
            ->where('pqd.language_id = ?', $languageId)
            ;

        $rowset = $select->fetchAll();
        if (!count($rowset)) {
            return false;
        }
        
        $result['answers'] = array();
        foreach ($rowset as $row) {
            $result['answers'][$row['answer_id']] = array(
                'id'          => $row['answer_id'],
                'question_id' => $row['id'],
                'answer'      => $row['answer'],
                'language_id' => $row['language_id']
            );
        }
        return array_merge(array(
            'languageId' => $row['language_id'],
            'id'         => $row['id'],
            'question'   => $row['question'],
            'type'       => $row['type']
        ), $result);
    }

    /**
     *
     * @uses  poll/index/index
     * @param int|bool $languageId
     * @param array|int $questionIds
     * @param int|bool $siteId
     * @return array
     */
    public function getQuestions(
        $languageId = false, $questionIds = array(), $siteId = false)
    {
        $select = $this->select(array('id', 'type'))
            ->joinLeft('poll_question_description', 
                'pqd.question_id = pq.id'
            )
            ->join('poll_question_site', 'pqs.question_id = pq.id')
            ->where('pq.status = 1')
            ->order('pq.id DESC')
            ;
        if ($languageId) {
            if (true === $languageId) {
                $languageId = Axis_Locale::getLanguageId();
            }
            $select->where('pqd.language_id = ?', $languageId);
        }
        
        if (!is_array($questionIds)) {
            $questionIds = array($questionIds);
        }
        if (count($questionIds)) {
            $select->where('pq.id IN(?)', $questionIds);
        }
        
        if ($siteId) {
            if (true === $siteId) {
                $siteId = Axis::getSiteId();
            }
            $select->where('pqs.site_id = ?', $siteId);
        }
        
        return $select->fetchAssoc();
    }

    /**
     *
     * @uses admin/poll_index/list
     * @return array
     */
    public function getQuestionsBack()
    {
        $questions = $this->select('*')
            ->joinLeft('poll_question_description', 
                'pqd.question_id = pq.id AND pqd.language_id = :languageId',
                'question'
            )->bind(array('languageId' => Axis_Locale::getLanguageId()))
            ->fetchAssoc()
        ;
        /* select assigned sites names*/
        $sitesNames = Axis::single('poll/question_site')->getSitesNamesAssigns();

        foreach (array_keys($questions) as $questionId) {
            $questions[$questionId]['sites'] = isset($sitesNames[$questionId]) ?
                implode(',', array_keys($sitesNames[$questionId])) : '';
        }

        $votes = Axis::single('poll/vote')->getVoteCount();
        foreach ($votes as $questionId => $count) {
            $questions[$questionId]['cnt'] = $count;
        }
        return array_values($questions);
    }
    
    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row 
     */
    public function save(array $data) 
    {
        $row = $this->getRow($data);
        $row->changed_at = Axis_Date::now()->toSQLString();
        if (null === $row->created_at) {
            $row->created_at = $row->changed_at;
        }
        $row->save();
        return $row;
    }
}