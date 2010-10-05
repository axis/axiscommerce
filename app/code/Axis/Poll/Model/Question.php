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
        $select = $this->select('pq.id')
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
        $query = 'SELECT `qd`.`language_id` as languageId, q.id,`qd`.`question`, `q`.`type`, `q`.`status` ' .
            "FROM " . $this->_prefix . 'poll_question' . " AS `q` " .
            'LEFT JOIN ' .  $this->_prefix . 'poll_question_description AS `qd` ON qd.question_id = q.id ' .
            'WHERE q.id = ?';

        return $this->getAdapter()->fetchAssoc($query, $questionId);
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
        $select = $this->getAdapter()->select()
            ->from(array('q' => $this->_prefix . 'poll_question'), array('id', 'type'))
            ->joinLeft(
                array('a' => $this->_prefix . 'poll_answer'),
                'a.question_id = q.id',
                array('answer_id' => 'id' , 'answer')
            )->joinLeft(
                array('qd' => $this->_prefix . 'poll_question_description'),
                'qd.question_id = q.id',
                array('question', 'language_id')
            )/*->joinLeft(
                array('ad' => $this->_prefix . 'poll_answer_description'),
                'ad.answer_id = a.id',
                array('answer_id', 'answer'))*/
            ->where('q.id = ?', $questionId)
            ->where('a.language_id = ? AND qd.language_id = ?', $languageId)
            ;

        if (!count($rowset = $this->getAdapter()->fetchAll($select))) {
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
        if (!is_array($questionIds)) {
            $questionIds = array($questionIds);
        }
        $qWhere = '';
        if (count($questionIds)) {
            $qWhere = $this->getAdapter()->quoteInto(
                ' AND q.id IN(?)', $questionIds
            );
        }

        if ($languageId) {
            if (true === $languageId) {
                $languageId = Axis_Locale::getLanguageId();
            }
            $lWhere = " AND qd.language_id = {$languageId}";
        }
        $sWhere = '';
        if ($siteId) {
            if (true === $siteId) {
                $siteId = Axis::getSiteId();
            }
            $sWhere = " AND s.site_id = {$siteId}";
        }
//        $ip = ip2long($_SERVER['REMOTE_ADDR']);

        $query = 'SELECT q.id, `qd`.`language_id` as languageId,`qd`.`question`, `q`.`type` ' .
            "FROM " . $this->_prefix . 'poll_question' . " AS `q` " .
            'LEFT JOIN ' . $this->_prefix . 'poll_question_description AS `qd` ON qd.question_id = q.id ' .
            "INNER JOIN " . $this->_prefix . "poll_question_site as s on s.question_id = q.id " .
//            "LEFT JOIN " . $this->_prefix . "poll_answer AS a ON a.question_id = q.id " .
//            "INNER JOIN " . $this->_prefix . "poll_vote AS v ON v.answer_id = a.id " .
            'WHERE q.status = 1' .
            $lWhere .
            $sWhere .
//            " AND v.ip = {$ip}" .
            $qWhere .
            " ORDER BY `q`.`id` DESC";

        return $this->getAdapter()->fetchAssoc($query);
    }

    /**
     *
     * @uses admin/poll_index/list
     * @return array
     */
    public function getQuestionsBack()
    {
        $query = "SELECT q.*, qd.question  FROM " . $this->_prefix . 'poll_question' . " AS q " .
            'LEFT JOIN ' . $this->_prefix . 'poll_question_description AS qd ' .
                'ON qd.question_id = q.id AND qd.language_id = ?';
        $questions = $this->getAdapter()->fetchAssoc(
            $query, Axis_Locale::getLanguageId()
        );
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
}