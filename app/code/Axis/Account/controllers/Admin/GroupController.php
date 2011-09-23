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
class Axis_Account_Admin_GroupController extends Axis_Admin_Controller_Back
{
    public  function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Manage Customer Groups'
        );
        $this->render();
    }
    
    public function listAction()
    {
        $alpha  = new Axis_Filter_DbField();
        $sort   = $alpha->filter($this->_getParam('sort', 'id'));
        $dir    = $alpha->filter($this->_getParam('dir', 'DESC'));
        $filter = $this->_getParam('filter', array());
        
        $dataset = Axis::single('account/customer_group')->select()
            ->order($sort . ' ' . $dir)
            ->where('id <> ?', Axis_Account_Model_Customer_Group::GROUP_ALL_ID)
            ->addFilters($filter)
            ->fetchAll()
            ;
        
        return $this->_helper->json
            ->setData($dataset)
            ->sendSuccess();
    }
    
    public function batchSaveAction()
    {
        $rowset = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($rowset)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        
        $model = Axis::single('account/customer_group');
        foreach ($rowset as $_row) {
            $model->save($_row);
        }
        Axis::message()->addSuccess(
            Axis::translate('account')->__(
                'Group was saved successfully'
            )
        );    
        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction()
    {
        $customerGroupIds = Zend_Json::decode($this->_getParam('data'));

        $isValid = true;

        if (in_array(Axis_Account_Model_Customer_Group::GROUP_GUEST_ID, $customerGroupIds)) {
            $isValid = false;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Your can't delete default Guest group id: %s ",
                    Axis_Account_Model_Customer_Group
                )
            );
        }

        if (in_array(Axis_Account_Model_Customer_Group::GROUP_ALL_ID, $customerGroupIds)) {
            $isValid = false;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Your can't delete default All group id: %s ",
                    Axis_Account_Model_Customer_Group::GROUP_ALL_ID
                )
            );
        }

        if (true === in_array(
                Axis::config()->account->main->defaultCustomerGroup,
                $customerGroupIds
            )) {

            $isValid = false;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Your can't delete default customer group id: %s ", $id
                )
            );
        }

        if (!sizeof($customerGroupIds)) {
            $isValid = false;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
        }
        
        if ($isValid) {
            Axis::single('account/customer_group')->delete(
                $this->db->quoteInto('id IN(?)', $customerGroupIds)
            );
            Axis::message()->addSuccess(
                Axis::translate('admin')->__(
                    'Group was deleted successfully'
                )
            );
        }
        $this->_helper->json->sendJson(array(
            'success' => $isValid
        ));
    }
}