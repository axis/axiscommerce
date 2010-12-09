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
class Axis_Poll_Model_Vote extends Axis_Db_Table
{
    protected $_name = 'poll_vote';

    public function hasVoteInTable($questionId, $customerId = false, $ip = null)
    {
        if (false === $customerId) {
            $customerId = Axis::getCustomerId();
        }
        if (null === $customerId) {
            $cWhere = 'AND pv.customer_id IS NULL ';
            if (null === $ip) {
                $ip = ip2long($_SERVER['REMOTE_ADDR']);
            }
            $ipWhere = $this->getAdapter()->quoteInto('AND pv.ip = ? ', $ip);
        } else {
            $cWhere = "AND pv.customer_id = {$customerId} ";
            $ipWhere = '';
            //usually not used
            if (null !== $ip) {
                $ipWhere = $this->getAdapter()->quoteInto('AND pv.ip = ? ', $ip);
            }
        }

        $query = "SELECT COUNT(*) FROM " . $this->_prefix . 'poll_vote' . " as  pv " .
            'INNER JOIN ' . $this->_prefix . 'poll_answer pa ON pa.id = pv.answer_id ' .
            "WHERE   pa.question_id = ? " .
            $ipWhere .
            $cWhere;
        return (bool) $this->getAdapter()->fetchOne($query, $questionId);
    }

    public function getVoteCount($customerId = false)
    {
        if (false === $customerId) {
            $cWhere = '';
        } elseif (null === $customerId) {
            $cWhere = 'AND v.customer_id IS NULL ';
        } else {
            $cWhere = "AND v.customer_id = {$customerId} ";
        }

        $rows =  $this->getAdapter()->fetchAll(
            "SELECT a.question_id, count(v.id) as cnt  FROM " . $this->_prefix . "poll_answer a
            LEFT JOIN " . $this->_prefix . "poll_vote AS v ON v.answer_id = a.id " .
            'WHERE a.language_id = ? ' .
            $cWhere .
            'GROUP BY a.question_id',
            Axis_Locale::getLanguageId()
        );
        $assigns = array();
        foreach ($rows as $row) {
            $assigns[intval($row['question_id'])] = intval($row['cnt']);
        }
        return $assigns;
    }

    public function getResults()
    {
        $rowset =  $this->getAdapter()->fetchAssoc(
            'SELECT pv.answer_id, pa.question_id, COUNT(*) as cnt ' .
            'FROM ' . $this->_prefix . 'poll_vote pv ' .
            'INNER JOIN ' . $this->_prefix . 'poll_answer pa ON pa.id = pv.answer_id ' .
            'WHERE pa.language_id = ? '.
            'GROUP BY pv.answer_id ' .
            'ORDER BY cnt',
            Axis_Locale::getLanguageId()
        );

        $assigns = array();
        foreach ($rowset as $row) {
            $assigns[$row['question_id']][$row['answer_id']] = $row;
        }
        return $assigns;
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