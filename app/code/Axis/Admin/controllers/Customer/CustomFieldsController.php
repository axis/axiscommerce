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
class Axis_Admin_Customer_CustomFieldsController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Custom Customer Fields'
        );
        $this->view->fieldTypes = array();
        $this->view->fieldValidators = array();

        $this->view->fieldGroups = Axis::model('account/Customer_FieldGroup')
            ->getGroups($this->_langId);
        $this->render();
    }
    
    public function getValidatorAction()
    {
        $fields = Axis::single('account/customer_field');
        
        $result = array();
        $i = 0;
        foreach ($fields->getValidators() as $key => $value) {
            $result[] = array($i++, $key, $value);
        }
        
        return $this->_helper->json->sendJson($result, false, false);
    }
    
    public function getTypeAction()
    {
        $fields = Axis::single('account/customer_field');
        
        $result = array();
        $i = 0;
        foreach ($fields->getFieldTypes() as $key => $value) {
            $result[] = array($i++, $key, $value);
        }
        
        return $this->_helper->json->sendJson($result, false, false);
    }
    
    public function getGroupsAction()
    {
        $this->getHelper('layout')->disableLayout();
        $data = Axis::model('account/Customer_FieldGroup')->getGroups(
            Axis_Locale::getLanguageId()
        );
        return $this->_helper->json->setData($data)->sendSuccess();
    }

    public function getFieldsAction()
    {
        $this->getHelper('layout')->disableLayout();
         
        return $this->_helper->json->sendSuccess(array(
            'data'  => Axis::single('account/customer_field')
                ->getFieldsByGroup((int) $this->_getParam('groupId'))
        ));
    }

    public function getGroupInfoAction()
    {
        $this->getHelper('layout')->disableLayout();
         
        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('account/Customer_FieldGroup')
                ->getCurrentGroup($this->_getParam('groupId'))
        ));
    }

    public function batchSaveFieldsAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        Axis::single('account/customer_field')->save($data); 

        $this->_helper->json->sendSuccess();
    }
    
    public function saveFieldAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getParam('data');
        
        Axis::single('account/customer_field')->save(array($data)); 

        $this->_helper->json->sendSuccess();
    }
    
    public function ajaxSaveGroupAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));
        
        $this->_helper->json->sendSuccess(array(
            'groupId'  => Axis::single('account/Customer_FieldGroup')
                ->save($data)
        ));
    }
    
    public function deleteFieldsAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        Axis::single('account/customer_field')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Field was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
    
    public function ajaxDeleteGroupAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = $this->_getParam('id');
        
        Axis::single('account/Customer_FieldGroup')
            ->delete($this->db->quoteInto('id IN(?)', $data));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Group was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    public function getValueSetsAction()
    {
        $this->getHelper('layout')->disableLayout();
        
        $result = array();
        $rowset = Axis::single('account/Customer_ValueSet')->fetchAll();

        foreach ($rowset as $row) {
            $result[] = array(
                'leaf' => true,
                'id' => $row->id,
                'text' => $row->name,
                'iconCls' => 'folder'
            );
        }
        
        $this->_helper->json->sendJson($result, false, false);
    }
    
    public function getValuesAction()
    {
        $this->_helper->layout->disableLayout();
        
        $valuesetId = (int) $this->_getParam('valuesetId');
        
        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('account/Customer_ValueSet_Value')
                ->getValues($valuesetId)
        ));
    }
    
    public function ajaxSaveValueSetAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));
        
        $this->_helper->json->sendSuccess(array(
            'valuesetId' => Axis::single('account/Customer_ValueSet')
                ->save($data)
        ));
    }
    
    public function ajaxDeleteValueSetAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = $this->_getParam('id');
        
        Axis::single('account/Customer_ValueSet')
            ->delete($this->db->quoteInto('id IN(?)', $data));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Group was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
    
    public function ajaxSaveValueSetValuesAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));
        $valueset = $this->_getParam('customer_valueset_id');
        
        Axis::single('account/Customer_ValueSet_Value')
            ->save($data, $valueset);
        
        $this->_helper->json->sendSuccess();
    }
    
    public function ajaxDeleteValueSetValuesAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        Axis::single('account/Customer_ValueSet_Value')
            ->delete($this->db->quoteInto('id IN(?)', $data));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Field was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
}