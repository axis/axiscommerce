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
class Axis_Poll_Model_Vote extends Axis_Db_Table
{
    protected $_name = 'poll_vote';

    public function hasVoteInTable($questionId, $customerId = false, $ip = null)
    {
        if (false === $customerId) {
            $customerId = Axis::getCustomerId();
        }

        $select = $this->select('*')
            ->join('poll_answer', 'pa.id = pv.answer_id')
            ->where('pa.question_id = ?', $questionId)
            ;
        if (null === $customerId) {
            $select->where('pv.customer_id IS NULL');
            if (null === $ip) {
                $ip = ip2long($_SERVER['REMOTE_ADDR']);
            }
            $select->where('pv.ip = ?', $ip);
        } else {
            $select->where('pv.customer_id = ?', $customerId);
            if (null !== $ip) {
                $select->where('pv.ip = ?', $ip);
            }
        }
        return (bool) $select->count();
    }

    public function getVoteCount($customerId = false)
    {
        $languageId = Axis_Locale::getLanguageId();
        
        $select = Axis::model('poll/answer')->select('question_id')
            ->joinLeft('poll_vote', 'pv.answer_id = pa.id', 'COUNT(pv.id)')
            ->where('pa.language_id = ?', $languageId)
            ->group('pa.question_id')
            ;
        
        if ($customerId) {
            $select->where('pv.customer_id = ?', $customerId);
        } elseif (null === $customerId) {
            $select->where('pv.customer_id IS NULL');
        }
        
        return $select->fetchPairs();
    }

    public function getResults()
    {
        $languageId = Axis_Locale::getLanguageId();
        
        $rowset = $this->select(array('answer_id', 'cnt' => 'COUNT(*)'))
            ->join('poll_answer', 'pa.id = pv.answer_id', 'question_id')
            ->where('pa.language_id = ?', $languageId)
            ->group('pv.answer_id')
            ->order('cnt')
            ->fetchAssoc()
            ;
        $dataset = array();
        foreach ($rowset as $row) {
            $dataset[$row['question_id']][$row['answer_id']] = $row;
        }
        return $dataset;
    }

    /**
     *
     * @return array
     */
    public function getQuestionIdsFromCookie($name = 'polls')
    {
        $questionIds = array();
        if (!isset($_COOKIE[$name])) {
            return array();
        }
        $questionIds = array();
        $items = array_filter(explode(',', $_COOKIE[$name]));

        foreach ($items as $item) {
            if (strstr($item, ':')) {
                list($questionId, $customerId) = explode(':', $item);
                $customerId = (int) $customerId;
                $questionId = (int) $questionId;
            } else {
                $questionId = (int) $item;
                $customerId = false;
            }
            if ($customerId == Axis::getCustomerId()) {
                $questionIds[$questionId] = $questionId;
            }
        }

        return array_values($questionIds);
    }

    public function addToCookie($values, $path, $time = 2592000, $name = 'polls')
    {
        if (empty($path)) {
            $path = '/';
        }

        $cookieValues = array();
        if (isset($_COOKIE[$name])) {
            $cookieValues = array_filter(explode(',', $_COOKIE[$name]));
        }

        if (!is_array($values)) {
            $values = array($values);
        }
        $customerId = Axis::getCustomerId();

        if ($customerId) {
            foreach ($values as &$value) {
                $value .= ':' . $customerId;
            }
        }

        $cookieValues = implode(',', array_unique(array_merge(
            $values, $cookieValues
        )));
        return setcookie($name, $cookieValues, time() + $time, $path);
    }
}