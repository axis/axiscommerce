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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_MailController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__(
            'Email Templates'
        );
        $this->view->sitesList = Axis::single('core/site')->fetchAll()->toArray();
        $this->render();
    }

    public function listAction()
    {
        $data = Axis::single('core/template_mail')->fetchAll()->toArray();
        
        return $this->_helper->json
            ->setData(array_values($data))
            ->setCount(count($data))
            ->sendSuccess();
    }
    
    public function loadAction() 
    {
        $templateId = $this->_getParam('templateId');
        
        $data = Axis::single('core/template_mail')->getInfo($templateId);
        
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();          
    }

    public function listEventAction()
    {
        $events = Axis::model('core/option_mail_event')->toArray();
        
        $data = array();
        foreach ($events as $id => $name) {
            $data[] = array(
                'id'   => $id, 
                'name' => $name
            );
        }
        
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
    
    public function listTemplateAction()
    {
        
        $data = array();
        foreach (Axis::model('core/option_mail_template') as $id => $name) {
            $data[] = array(
                'id'   => $id,
                'name' => $name
            );
        }
        
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
    
    public function listMailAction()
    {
        $mailBoxes = Axis::model('core/option_mail_boxes')->toArray();
        
        $data = array();
        foreach ($mailBoxes as $id => $name) {
            $data[] = array(
                'id' => $id, 
                'name' => $name
            );
        }
        
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        if ($this->_getParam('data', false)) {
            $data = Zend_Json::decode($this->_getParam('data'));
        } else {
            $id = $this->_getParam('id', 0);
            $data[$id] = $this->_getAllParams();
            unset($data[$id]['module']);
            unset($data[$id]['controller']);
            unset($data[$id]['action']);
        }
        
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
            ));
            return $this->_helper->json->sendFailure();
        }
        $model = Axis::model('core/template_mail');
        foreach ($data as $_row) {
            /* saving content into file */ 
            if (!empty($_row['content'])) {
                
                $file = AXIS_ROOT. '/app/design/mail/' 
                      . $_row['template'] . '_' . $_row['type'] . '.phtml';

                if (!is_writable($file)) {
                    Axis::message()->addError(
                        Axis::translate('core')->__(
                            "Can't open file with write permissions : %s", $file
                    ));
                } else if (!@file_put_contents($file, $_row['content'])) {
                    Axis::message()->addError(
                        Axis::translate('core')->__(
                            "Can't write content to file :%s", $file
                    ));
                }
            }
            
            $model->save($_row);
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction()
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
        Axis::single('core/template_mail')->delete(
            $this->db->quoteInto('id IN(?)', $ids)
        );

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}