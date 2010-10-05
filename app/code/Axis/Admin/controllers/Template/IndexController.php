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
class Axis_Admin_Template_IndexController extends Axis_Admin_Controller_Back 
{
    /**
     * Template model
     *
     * @var Axis_Admin_Model_Template
     */
    protected $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('core/template');
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Templates');
		
        $pages = array();
        $orderBy = array('module_name', 'controller_name', 'action_name');
        foreach (Axis::single('core/page')->fetchAll(null, $orderBy) as $row) {
            $pages[] = array(
                'id' => $row->id,
                'name' => $row->module_name . '/' . $row->controller_name . '/' . $row->action_name
            );
        }
        
        $this->view->pages = $pages;
        
        $this->view->boxClasses = Axis::single('core/template_box')->getList();
        
        $this->render();
    }

    public function getNodesAction()
    {
        $nodes = $this->_table->fetchAll();
        
        foreach ($nodes as $item) {
            $result[] = array(
                'text' => $item->name,
                'id'   => $item->id,
                'leaf' => false,
                'cls' => $item->is_active ? '' : 'disabledNode',
                'children' => array(),
                'expanded' => true
            );
        }
        
        $this->_helper->json->sendJson($result, false, false);
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = $this->_getAllParams();
        
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('core/template')->save($data)
        ));
    }
    
    public function getInfoAction() 
    {
        $this->_helper->layout->disableLayout();
        
        $templateId = $this->_getParam('templateId');
        
        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('core/template')->getInfo($templateId)
        ));           
    }
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        
        $id = $this->_getParam('templateId', false);
        
        $usedTemplates = Axis::single('core/template')->getUsed();
        
        if (!$id || in_array($id, $usedTemplates)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Template is used already and can't be deleted"
                )
            );
            return $this->_helper->json->sendFailure();
        }
        
        $this->_table->delete($this->db->quoteInto('id = ? ', $id));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Template was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function listXmlTemplatesAction()
    {
        $this->_helper->layout->disableLayout();
        
        return $this->_helper->json->sendSuccess(array(
            'data' => $this->_table->getListXmlFiles()
        ));
    }

    public function importAction()
    {
        $this->_helper->layout->disableLayout();
        
        if (!$this->_getParam('overwrite_existing') && 
            !$this->_table->validateBeforeImport($this->_getParam('templateName'))) {
            
            return $this->_helper->json->sendFailure(array(
                'errorCode' => 'template_exists'
            ));
        }
        
        if (!$this->_table->importTemplateFromXmlFile($this->_getParam('templateName'))) {
            return $this->_helper->json->sendFailure();
        }
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Template was imported successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function exportAction()
    {
        $this->_helper->layout->disableLayout();
        $templateId = $this->_getParam('templateId');
        $template = $this->_table->getFullInfo($templateId);
        $this->view->template = $template;
        $script = $this->getViewScript('xml', false);
        $xml = $this->view->render($script);

        $filename = Axis::config()->system->path . '/var/templates/' . $template['name'] . '.xml';
        if (@file_put_contents($filename, $xml)) {
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Template was exported successfully'
                )
            );
            return $this->_helper->json->sendSuccess();
        }
        Axis::message()->addError(
            Axis::translate('admin')->__(
                "Can't write to file %s", $filename
            )
        );
        return $this->_helper->json->sendFailure();
    }
}