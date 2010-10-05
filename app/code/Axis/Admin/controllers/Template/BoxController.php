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
class Axis_Admin_Template_BoxController extends Axis_Admin_Controller_Back
{
    private $_templateId;
    
    /**
     * Box model
     *
     * @var Axis_Core_Model_Template_Box
     */
    protected $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('core/template_box');
        $this->_helper->layout->disableLayout();
        $this->_templateId = (int) $this->_getParam('tId', 0);
    }
    
    private function _getBoxes()
    {
        $boxes = array();
        foreach (Axis::single('core/template_box')->getList() as $box) {
            $type = 'dynamic';
            if (strstr($box, 'Axis_Cms_Block')) {
                $type = 'static'; 
            }
            $boxes[$type][$box] = $box;
        }
        return $boxes;
    }
    
    private function _initForm()
    {
        $this->view->templateBoxes = $this->_getBoxes();
        $pageTable = Axis::single('core/page');
        $pages = array();
        $orderBy = array('module_name', 'controller_name', 'action_name');
        foreach ($pageTable->fetchAll(null, $orderBy) as $row) {
            $pages[$row->module_name][$row->controller_name][$row->id] = $row->action_name;
        }
        $this->view->pages = $pages;
    }
    
    public function indexAction()
    {
        $where = NULL;
        if ($this->_templateId) {
            $where = 'template_id = ' . $this->_templateId;
        }
        $boxes = $this->_table->fetchAll($where)->toArray();
        Zend_Debug::dump($boxes);
    }
    
    public function listAction()
    {
        $boxes = $this->_table
            ->fetchAll('template_id = ' . $this->_templateId, 'id DESC')
            ->toArray();
        foreach ($boxes as &$box) {
            $show = Axis::single('core/template_box_page')
                ->fetchAll(array('box_id = ' . $box['id']));
                
            $pages = array();
            foreach ($show as $item)
                $pages[] = $item->page_id;
            $box['show'] = implode(',', $pages);
        }
        return $this->_helper->json->sendSuccess(array(
            'data' => $boxes
        ));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $params = $this->_getAllParams();
        $boxId = $this->_getParam('boxId', 0);
        $params['id'] = $boxId;
        $data[$boxId] = $params;
        Axis::single('core/template_box')
            ->save($this->_templateId, $data);
        
        return $this->_helper->json->sendSuccess();
    }
    
    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        return $this->_helper->json->sendJson(array('success' =>
            Axis::single('core/template_box')->save(
                $this->_templateId, $data, 'batch'
        )));
    }
    
    public function editAction()
    {
        $boxId = $this->_getParam('boxId');
        $row = $this->_table->find($boxId)->current();
        $this->view->box = $row->toArray();
        $this->view->boxId = $boxId;
        $this->view->templateId = $this->_templateId;
        
        /* assignments */
        $boxToPage = Axis::single('core/template_box_page');
        $assignments = array();
        foreach ($boxToPage->fetchAll('box_id = ' . intval($boxId)) as $item) {
            $assignments[$item->page_id] = $item->toArray();
        }
        $this->view->assignments = $assignments;
        
        $this->_initForm();
        $this->render('form');
    }
    
    public function deleteAction()
    {
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        
        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        
        $this->_table->delete($this->db->quoteInto('id IN (?)', $ids));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}