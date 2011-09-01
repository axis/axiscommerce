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
class Axis_Account_Admin_ValueSetValueController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $valuesetId = (int) $this->_getParam('valuesetId');
        $data = Axis::model('account/Customer_ValueSet_Value')
                ->getValues($valuesetId);
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
       
    public function saveAction()
    {
        $_rowset     = Zend_Json::decode($this->_getParam('data'));
        $valuesetId  = $this->_getParam('customer_valueset_id');
        $model       = Axis::single('account/Customer_ValueSet_Value');
        $modelLabel  = Axis::single('account/Customer_ValueSet_Value_Label');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        foreach ($_rowset as $_row) {
            if (!isset($_row['customer_valueset_id'])) {
                $_row['customer_valueset_id'] = $valuesetId;
            }
            $row = $model->getRow($_row);
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
        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('account/Customer_ValueSet_Value')
            ->delete($this->db->quoteInto('id IN(?)', $data));
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Field was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}