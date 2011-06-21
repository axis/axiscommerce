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
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
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
        $questionId = $this->_getParam('id');
        
        $data = array(
            'id'    => $questionId,
            'sites' => Axis::single('poll/question_site')
                ->getSitesIds($questionId)
        );
        
        $question = Axis::single('poll/question')
            ->getQuestionById($questionId);
        foreach ($question as $lngQuestion) {
            $data['status'] = $lngQuestion['status'];
            $data['type'] = $lngQuestion['type'];
            $data['description'][$lngQuestion['languageId']] = $lngQuestion['question'];
        }
        
        $answers = Axis::single('poll/answer')->getAnswers(
            false, $questionId
        );
        
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
        $questionId = $this->_getParam('id', false);
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
        $data        = $this->_getAllParams();
        $model       = Axis::model('poll/question');
        $modelLabel  = Axis::model('poll/question_description');
        $modelSite   = Axis::model('poll/question_site');
        $modelAnswer = Axis::single('poll/answer');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        $row = $model->save($data);
        
        //save description
        foreach ($languageIds as $languageId) {
            $rowDescription = $modelLabel->getRow($row->id, $languageId);
            $rowDescription->question = $data['description'][$languageId];
            $rowDescription->save();
        }
        
        //save site relation
        $modelSite->delete(
            $this->db->quoteInto('question_id = ?', $row->id)
        );
        $sites = array_filter(
            explode(',', $data['sites'])
        );
        foreach ($sites as $siteId) {
            
            $modelSite->createRow(array(
                'question_id' => $row->id, 
                'site_id'     => (int) $siteId
            ))->save();
        }
        
        //save answers 
        $answers = $this->_getParam('answer', array());
        if (2 > count($answers)) {
            Axis::message()->addNotice(
                Axis::translate('poll')->__(
                    'Define at least one answers.'
            ));
        }
        $modelAnswer->delete(
            $this->db->quoteInto('question_id = ?', $row->id)
        );
        foreach ($answers as $answerId => $_dataset) {
            foreach ($_dataset as $languageId => $answer) {
                $data = array(
                    'language_id' => $languageId,
                    'question_id' => $row->id,
                    'answer'      => $answer
                    
                );
                if (0 < $answerId) {
                    $data['id'] = $answerId;
                }
                $rowAnswer = $modelAnswer->createRow($data);
                $ret = $rowAnswer->save();
                $answerId = $rowAnswer->id;
            }
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
        $dataset = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$dataset)  {
            return $this->_helper->json->sendFailure();
        }
        $model     = Axis::model('poll/question');
        $modelSite = Axis::model('poll/question_site');
        
        foreach ($dataset as $_row) {
            $row = $model->save($_row);
            //save site relation
            $modelSite->delete(
                $this->db->quoteInto('question_id = ?', $row->id)
            );
            $sites = array_filter(explode(',', $_row['sites']));
            foreach ($sites as $siteId) {
                $modelSite->createRow(array(
                    'question_id' => $row->id, 
                    'site_id'     => (int) $siteId
                ))->save();
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
            $answersIds = array_merge($modelAnswer->select('id')
                ->where('question_id = ?', $questionId)
                ->fetchCol(), $answersIds
            );
        }
        $answersIds = array_unique($answersIds);
        Axis::single('poll/vote')->delete(
            $this->db->quoteInto('answer_id IN(?)', $answersIds)
        );

        $this->_helper->json->sendSuccess();
    }
}
