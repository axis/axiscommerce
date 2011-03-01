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
 * @subpackage  Axis_Poll_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Poll
 * @subpackage  Axis_Poll_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Poll_IndexController extends Axis_Core_Controller_Front
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('poll')->__('Polls');
        $this->view->meta()->setTitle($this->view->pageTitle);
        $languageId = Axis_Locale::getLanguageId();


        $questionIds = array();
        if ($this->_hasParam('questionId'))  {
            $questionIds[] = array('id' => $this->_getParam('questionId'));
        }
        $modelVote = Axis::single('poll/vote');
        $modelAnswer = Axis::single('poll/answer');

        $questions = Axis::single('poll/question')->getQuestions(
            $languageId, $questionIds
        );

        $answers = array();
        foreach ($modelAnswer->getAnswers($languageId) as $answer) {
            $answers[$answer['question_id']][] = $answer;
        }

        $votes = $modelVote->getVoteCount();
        $customerVotes = $modelVote->getVoteCount(Axis::getCustomerId());
        $results = $modelVote->getResults();

        $cookieVotedQuestionIds = $modelVote->getQuestionIdsFromCookie();

        $showResult = (bool) $this->_getParam('showResult', false);

        foreach ($questions as &$question) {
            $question['answer'] = isset($answers[$question['id']]) ?
                $answers[$question['id']] : array();

            $isVoted = $showResult
                || in_array($question['id'], $cookieVotedQuestionIds)
                || (isset($customerVotes[$question['id']]) && 0 < $customerVotes[$question['id']]);

            $question['status'] = false;
            if ($isVoted) {
                $question['results'] = isset($results[$question['id']])
                    ? $results[$question['id']] : array();
                $question['totalCount'] = $votes[$question['id']];
                $question['status'] = true;
            }
        }
        $this->view->questions = $questions;
        $this->render('all');
    }

    protected function _ajaxSaveResponse($questionId)
    {
        $this->_helper->layout->disableLayout();
        $htmlBoxContent = $this->view->box('poll/poll')->refresh()
            ->setFromArray(array(
                'question_id'     => $questionId,
                'show_result'     => true,
                'disable_wrapper' => true
            ))
            ->render();

        return $this->_helper->json->sendSuccess(
            array('content' => $htmlBoxContent)
        );
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $questionId = current($this->_getParam('questionId'));

        $modelPollVote = Axis::single('poll/vote');
        $oldCookieValues = $modelPollVote->getQuestionIdsFromCookie();

        $inCookie = in_array($questionId, $oldCookieValues);

        if (!$inCookie) {
            $modelPollVote->addToCookie($questionId, $this->view->baseUrl());
        }

        if ($inCookie || $modelPollVote->hasVoteInTable($questionId)) {
            Axis::message()->addError(Axis::translate('poll')->__(
                'You have voted in this poll already'
            ));
            if ($this->_request->isXmlHttpRequest()) {
                return $this->_ajaxSaveResponse($questionId);
            }
            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
        }

        $customerId = Axis::getCustomerId();
        $data = array(
           'ip'          => ip2long($this->getRequest()->getServer('REMOTE_ADDR')),
           'created_at'  => Axis_Date::now()->toSQLString(),
           'visitor_id'  => Axis::single('log/visitor')->getVisitor()->id,
           'customer_id' => $customerId ? $customerId : new Zend_Db_Expr('NULL')
        );

        $votes = $this->_getParam('vote');
        $isMulti = Axis::single('poll/question')
            ->find($questionId)
            ->current()
            ->isMultiQuestion();

        $modelAnswer = Axis::single('poll/answer');
        if (!empty($votes)) {
            foreach ($votes as $voteId) {
                //  checking answer depend.. question
                if ($modelAnswer->getAttitude($questionId, $voteId)) {
                    $data['answer_id'] = $voteId;
                    $modelPollVote->insert($data);
                }
                if (!$isMulti) {
                    break;
                }
            }
        }

        if ($this->_request->isXmlHttpRequest()) {
            return $this->_ajaxSaveResponse($questionId);
        }
        $this->_redirect('/poll/index/index/questionId/' . $questionId);
    }
}
