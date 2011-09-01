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
class Axis_Account_Admin_FieldGroupController extends Axis_Admin_Controller_Back
{      
    public function listAction()
    {
        $data = Axis::model('account/Customer_FieldGroup')->getGroups(
            Axis_Locale::getLanguageId()
        );
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function loadAction()
    {
        $groupId = $this->_getParam('groupId');
        $data = Axis::model('account/Customer_FieldGroup')->getCurrentGroup($groupId);
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
    
    public function saveAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $data['name'] = preg_replace(
            array("/[^a-z0-9\s+]/", "/\s+/"),
            array('', '_'),
            strtolower($data['name'])
        );
        
        $row = Axis::model('account/Customer_FieldGroup')->save($data);
        Axis::message()->addSuccess(
            Axis::translate('account')->__(
                'Group was saved successfully'
            )
        );
        
        $languageIds = array_keys(Axis_Collect_Language::collect());
        $modelLabel = Axis::model('account/Customer_FieldGroup_Label');
        foreach ($languageIds as $languageId) {
            $rowLabel = $modelLabel->getRow($row->id, $languageId);
            $rowLabel->group_label = $data['group_label-' . $languageId];
            $rowLabel->save();
        }
        
        $this->_helper->json
            ->setGroupId($row->id)
            ->sendSuccess();
    }
    
    public function removeAction()
    {
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
}