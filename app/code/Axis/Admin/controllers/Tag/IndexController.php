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
class Axis_Admin_Tag_IndexController extends Axis_Admin_Controller_Back
{
    /**
     *
     * @var Axis_Tag_Model_Customer
     */
    private $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('tag/customer');
    }
    
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('tag')->__('Tags');
        if ($this->_hasParam('tagId')) {
            $this->view->tagId = $this->_getParam('tagId');
        }
        $this->render();
    }
    
    public function listAction()
    {
        $field = new Axis_Filter_DbField();
        if ($this->_hasParam('tagId')) {
            $this->view->tagId = $this->_getParam('tagId');
        }
        $params = array(
            'start' => (int) $this->_getParam('start', 0),
            'limit' => (int) $this->_getParam('limit', 20),
            'sort' => $field->filter($this->_getParam('sort', 'id')),
            'dir' => $field->filter($this->_getParam('dir', 'DESC')),
            'languageId' => $this->_langId,
            'filters' => $this->_getParam('filter', array())
        );
        
        return $this->_helper->json->sendSuccess(array(
            'data' => $this->_table->getList($params),
            'count' => $this->_table->getCount($params)
        ));
    }
    
    public function deleteAction()
    {
        $this->layout->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            return;
        }
        
        $this->_helper->json->sendJson(array(
            'success' => (bool) $this->_table->delete(
                $this->db->quoteInto('id IN(?)', $data)
            )
        ));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$data) {
            return $this->_helper->json->sendFailure();
        }
        $success = true;
        foreach ($data as $tagRow) {
            $tag = $this->_table->find($tagRow['id'])->current();
            $tag->status = $tagRow['status'];
            $success = $success && (bool)$tag->save();
        }    
        
        $this->_helper->json->sendJson(array('success' => $success));
    }
}