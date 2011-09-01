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
        $this->view->fieldGroups = Axis::model('account/Customer_FieldGroup')
            ->getGroups(Axis_Locale::getLanguageId());
        $this->render();
    }
    
    public function listTypeAction()
    {
        $fieldTypes = Axis::model('account/customer_field')->getFieldTypes();
        $data = array();
        $i = 0;
        foreach ($fieldTypes as $key => $value) {
            $data[] = array($i++, $key, $value);
        }
        return $this->_helper->json->sendRaw($data);
    }
    
    public function listValidatorAction()
    {
        $validators = Axis::model('account/customer_field')->getValidators();
        $data = array();
        $i = 0;
        foreach ($validators as $key => $value) {
            $data[] = array($i++, $key, $value);
        }
        return $this->_helper->json->sendRaw($data);
    }

    public function listAction()
    {
        $groupId = (int) $this->_getParam('groupId');
        $data = Axis::model('account/customer_field')->getFieldsByGroup($groupId);
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $_row = $this->_getParam('data');
        $row  = Axis::model('account/customer_field')->save($_row); 
//        $row->save();
        $languageIds = array_keys(Axis_Collect_Language::collect());
        $modelLabel = Axis::model('account/customer_field_label');
        foreach ($languageIds as $languageId) {
            $rowLabel = $modelLabel->getRow($row->id, $languageId);
            $rowLabel->field_label = $_row['field_label' . $languageId];
            $rowLabel->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function batchSaveAction()
    {
        $_rowset     = Zend_Json::decode($this->_getParam('data'));
        $model       = Axis::model('account/customer_field');
        $modelLabel  = Axis::model('account/customer_field_label');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        foreach ($_rowset as $_row) {
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
        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('account/customer_field')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Field was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}