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
class Axis_Account_Admin_FieldController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Custom Customer Fields'
        );
        $this->view->fieldTypes = array();
        $this->view->fieldValidators = array();

        $this->view->fieldGroups = Axis::model('account/Customer_FieldGroup')
            ->getGroups(Axis_Locale::getLanguageId());
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
        
        return $this->_helper->json->sendRaw($result);
    }
    
    public function getTypeAction()
    {
        $fields = Axis::single('account/customer_field');
        
        $result = array();
        $i = 0;
        foreach ($fields->getFieldTypes() as $key => $value) {
            $result[] = array($i++, $key, $value);
        }
        
        return $this->_helper->json->sendRaw($result);
    }

    public function getFieldsAction()
    {
        $this->getHelper('layout')->disableLayout();
         
        return $this->_helper->json->sendSuccess(array(
            'data'  => Axis::single('account/customer_field')
                ->getFieldsByGroup((int) $this->_getParam('groupId'))
        ));
    }

    public function batchSaveFieldsAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        $model       = Axis::model('account/customer_field');
        $modelLabel  = Axis::model('account/Customer_Field_Label');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        foreach ($data as $_row) {
            $row = $model->save($_row);
            foreach ($languageIds as $languageId) {
                $rowLabel = $modelLabel->getRow($row->id, $languageId);
                $rowLabel->field_label = $_row['field_label' . $languageId];
                $rowLabel->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
    
    public function saveFieldAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getParam('data');
        
        $row = Axis::model('account/customer_field')->save($data); 
//        $row->save();
        $languageIds = array_keys(Axis_Collect_Language::collect());
        $modelLabel = Axis::model('account/Customer_Field_Label');
        foreach ($languageIds as $languageId) {
            $rowLabel = $modelLabel->getRow($row->id, $languageId);
            $rowLabel->field_label = $data['field_label' . $languageId];
            $rowLabel->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        $this->_helper->json->sendSuccess();
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
   
    public function getValuesAction()
    {
        $this->_helper->layout->disableLayout();
        
        $valuesetId = (int) $this->_getParam('valuesetId');
        
        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('account/Customer_ValueSet_Value')
                ->getValues($valuesetId)
        ));
    }
       
    public function ajaxSaveValueSetValuesAction()
    {
        $this->_helper->layout->disableLayout();
        $dataset = Zend_Json::decode($this->_getParam('data'));
        $valuesetId = $this->_getParam('customer_valueset_id');
        
        $modelValue  = Axis::single('account/Customer_ValueSet_Value');
        $modelLabel  = Axis::single('account/Customer_ValueSet_Value_Label');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        foreach ($dataset as $_row) {
            if (!isset($_row['customer_valueset_id'])) {
                $_row['customer_valueset_id'] = $valuesetId;
            }
            $row = $modelValue->getRow($_row);
            $row->save();
            foreach ($languageIds as $languageId) {
                $rowLabel = $modelLabel->getRow($row->id, $languageId);
                $rowLabel->label = $_row['label' . $languageId];
                $rowLabel->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );

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