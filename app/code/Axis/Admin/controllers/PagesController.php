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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_PagesController extends Axis_Admin_Controller_Back
{
    protected $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('core/page');
    }
    
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('cms')->__('Pages');
        $this->render();
    }
    
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = $this->_table->fetchAll()->toArray();
        
        return $this->_helper->json->sendSuccess(array(
            'data' => array_values($data)
        ));
    }
    
    public function saveAction()
    {   
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        $success = Axis::single('core/page')->save($data);
        
        $this->_helper->json->sendJson(array(
            'success' => $success
        ));        
    }
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        
        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $this->_table->delete($this->db->quoteInto('id IN(?)', $ids));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}
