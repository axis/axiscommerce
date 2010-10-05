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
 * @package     Axis_Admin
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Poll_IndexController extends Axis_Admin_Controller_Back
{
    /**
     * Question model
     *
     * @var Axis_Poll_Model_Question
     */
    protected $_table;
    
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('poll')->__('Polls');
        $this->view->sitesList = Axis::single('core/site')
            ->fetchAll()->toArray();
        $this->render();
    }
    
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $questions = Axis::single('poll/question')->getQuestionsBack();
        $this->_helper->json->sendSuccess(array('data' => $questions));
    }
    
    public function getQuestionAction()
    {
        $this->_helper->layout->disableLayout();
        $questionId = $this->_getParam('questionId');
        
        $answers = Axis::single('poll/answer')->getAnswers(
            false, $questionId
        );
        $question = Axis::single('poll/question')
            ->getQuestionById($questionId);
        $data = array(
            'questionId' => $questionId,
            'sites' => Axis::single('poll/question_site')
                ->getSitesIds($questionId)
        );
        foreach ($question as $lngQuestion) {
            $data['status'] = $lngQuestion['status'];
            $data['type'] = $lngQuestion['type'];
            $data['text'][$lngQuestion['languageId']] = $lngQuestion['question'];
        }
        foreach ($answers as $answer) {
            $data['answer'][$answer['id']]['text'][$answer['language_id']]
                = $answer['answer'];
        }
        $this->_helper->json->sendSuccess(
            array('data' => array($data))
        );
    }
    public function getResultAction()
    {
        $this->_helper->layout->disableLayout();
        $questionId = $this->_getParam('questionId', false);
        if (!$questionId) {
            return $this->_helper->json->sendFailure();
        }
        $results = Axis::single('poll/question')
            ->find($questionId)->current()->getResults();
        $answers = Axis::single('poll/answer')->getAnswers(
            Axis_Locale::getLanguageId(), $questionId
        );
        foreach ($answers as &$answer) {
            $answer['count'] = isset($results[$answer['id']]['cnt']) ?
                $results[$answer['id']]['cnt'] : 0;
        }
        $this->_helper->json->sendSuccess(
            array('data' => $answers)
        );
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $params = $this->_getAllParams();

        $questionData = array(
           'changed_at' => Axis_Date::now()->toSQLString(),
           'status' => $this->_getParam('status', 0),
           'type' => $this->_getParam('type', 0)
        );
        
        /*
         * Saving question
         */
        if (!$questionId = $this->_getParam('questionId', false)) {
	        $questionData['created_at'] = Axis_Date::now()->toSQLString();
	        $questionId = Axis::single('poll/question')
                ->insert($questionData);
        } else {
            Axis::single('poll/question')->update(
                $questionData,
                $this->db->quoteInto('id = ?', $questionId)
            );
        }
        $modelQuestionDescription = Axis::single('poll/question_description');
        $modelQuestionSite = Axis::single('poll/question_site');
        foreach (Axis_Collect_Language::collect() as $languageId => $language) {
            if (!isset($params['question'][$languageId])){
                continue;
            }
            $modelQuestionDescription->save(
                $questionId, $languageId, $params['question'][$languageId]
            );
        }    
        if (isset($params['sites'])) {
            $modelQuestionSite->delete(
                $this->db->quoteInto('question_id = ?', $questionId)
            );
            foreach (explode(',', $params['sites']) as $siteId) {
                if (empty ($siteId)) {
                    continue;
                }
                $modelQuestionSite->insert(array(
                    'question_id' => $questionId, 'site_id' => (int) $siteId
                ));
            }
        }
        
        /**
         * Saving answers
         */

        /**
         *  @var Axis_Poll_Model_Answer $modelAnswer
         */
        $modelAnswer = Axis::single('poll/answer');
        if (isset($params['deleteAnswerIds'])) {
            $modelAnswer->delete(
                $this->db->quoteInto('id IN (?)', $params['deleteAnswerIds'])
            );
        }
        
        if (isset($params['answer'])) {
            foreach ($params['answer'] as $answerId => $answerRowset) {
                $realAnswerId = $answerId;
                foreach ($answerRowset as $languageId => $answerText) {
                    $result = $modelAnswer->save(
                        $realAnswerId, $languageId, $questionId, $answerText
                    );
                    $realAnswerId = (int) $result['id'];
                }
            }
        } else {
            Axis::message()->addNotice(
                Axis::translate('poll')->__(
                    'Define at least one answers.'
            ));
        }
        $this->_helper->json->sendSuccess();
    }       
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$ids) {
            return $this->_helper->json->sendFailure();
        }
        Axis::single('poll/question')
            ->delete($this->db->quoteInto('id IN(?)', $ids));
        $this->_helper->json->sendSuccess();
    }
    
    public function quickSaveAction()
    {        
        $this->_helper->layout->disableLayout();
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$data)  {
            return $this->_helper->json->sendFailure();
        }
        $modelQuestionSite = Axis::single('poll/question_site');
        $modelQuestion = Axis::single('poll/question');
        foreach ($data as $questionId => $question) {
            $modelQuestion->update(
                array(
		            'changed_at' => Axis_Date::now()->toSQLString(),
		            'status' => $question['status'] ? 1 : 0,
                    'type' => $question['type'] ? 1 : 0
	            ), 
	            $this->db->quoteInto('id = ?', $questionId)
	        );
            $modelQuestionSite->delete(
                $this->db->quoteInto('question_id = ?', $questionId)
            );
            foreach (explode(',', $question['sites']) as $siteId) {
                if (empty ($siteId)) {
                    continue;
                }
                $modelQuestionSite->insert(array(
                    'question_id' => $questionId, 'site_id' => (int) $siteId
                ));
            }
        }
        $this->_helper->json->sendSuccess();
    }
    
    public function clearAction()
    {
        $this->_helper->layout->disableLayout();
        $paramQuestionIds = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$paramQuestionIds) {
            return $this->_helper->json->sendFailure();
        }
        $answersIds = array();
        $modelAnswer = Axis::single('poll/answer');
        foreach ($paramQuestionIds as $questionId)  {
            $answersIds = array_merge(
                $modelAnswer->getIdsByQuestionId($questionId), $answersIds
            );
        }
        $answersIds = array_unique($answersIds);
        Axis::single('poll/vote')->delete(
            $this->db->quoteInto('answer_id IN(?)', $answersIds)
        );

        $this->_helper->json->sendSuccess();
    }
}
