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
class Axis_Admin_Cms_BlockController extends Axis_Admin_Controller_Back 
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Static Blocks');
        
        $this->render();
    }
    
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        
        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('admin/cms_block')->getList()
        ));
    }
    
    public function getDataAction()
    {
        $this->_helper->layout->disableLayout();
        
        if (!$row = Axis::single('admin/cms_block')
                ->find($this->_getParam('id'))->current()) {
            
            Axis::message()->addError(Axis::translate('Axis_Cms')->__(
                'Block %s not found', $this->_getParam('id')
            ));
            return $this->_helper->json->sendFailure();
        }
        
        $this->_helper->json->sendSuccess(array(
            'data' => $row->toArray()
        ));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        Axis::single('cms/block')->save($this->_getAllParams());
        Axis::message()->addSuccess(Axis::translate()->__(
            'Data was saved successfully'
        ));
        $this->_helper->json->sendSuccess();
    }
    
    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        
        $tableBlock = Axis::single('cms/block');
        
        foreach ($data as $values) {
            $tableBlock->save($values);
        }
        Axis::message()->addSuccess(Axis::translate()->__(
            'Data was saved successfully'
        ));
        $this->_helper->json->sendSuccess();
    }
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('admin/cms_block')->delete(
                $this->db->quoteInto('id IN(?)', $data)
            )
        ));
    }
}