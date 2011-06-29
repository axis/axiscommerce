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
class Axis_Admin_Template_MailController extends Axis_Admin_Controller_Back
{
    protected $_table;

    public function init()
    {
        parent::init();
        $this->getHelper('layout')->disableLayout();
        $this->_table = Axis::single('core/template_mail');
    }

    public function indexAction()
    {
        $this->_helper->layout->enableLayout();
        $this->view->pageTitle = Axis::translate('admin')->__(
            'Email Templates'
        );
        $this->view->sitesList = Axis::single('core/site')->fetchAll()->toArray();
        $this->render();
    }

    public function listAction()
    {
        $data = $this->_table->fetchAll()->toArray();
        $this->_helper->json->sendSuccess(array(
            'data' => array_values($data),
            'count'   => count($data)
        ));
    }

    public function listEventAction()
    {
        $events = Axis_Collect_MailEvent::collect();
        
        $result = array();
        $i = 0;
        foreach ($events as $eventKey => $event) {
            $result[$i] = array('name' => $event, 'id' => $eventKey);
            $i++;
        }
        
        $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
    }
    
    public function listTemplateAction()
    {
        $templates = Axis_Collect_MailTemplate::collect();
        
        $result = array();
        $i = 0;
        foreach ($templates as $templateKey => $template) {
            $result[$i] = array('name' => $template, 'id' => $templateKey);
            $i++;
        }
        
        $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
    }
    
    public function listMailAction()
    {
        $templates = Axis_Collect_MailBoxes::collect();
        
        $result = array();
        $i = 0;
        foreach ($templates as $templateKey => $template) {
            $result[$i] = array('name' => $template, 'id' => $templateKey);
            $i++;
        }
        
        $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        if ($this->_getParam('data', false)) {
            $data = Zend_Json::decode($this->_getParam('data'));
        } else {
            $id = $this->_getParam('id', 0);
            $data[$id] = $this->_getAllParams();
            unset($data[$id]['module']);
            unset($data[$id]['controller']);
            unset($data[$id]['action']);
        }
        
        return $this->_helper->json->sendJson(array(
            'success' => Axis::single('core/template_mail')->save($data)
        ));
    }
    
    public function deleteAction()
    {
        $ids = Zend_Json::decode($this->_getParam('data'));
        
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
    
    public function getInfoAction() 
    {
        $this->_helper->layout->disableLayout();
        
        $templateId = $this->_getParam('templateId');
        
        $info = Axis::single('core/template_mail')->getInfo($templateId);
        
        return $this->_helper->json->sendSuccess(array(
            'data' => $info
        ));          
    }
}