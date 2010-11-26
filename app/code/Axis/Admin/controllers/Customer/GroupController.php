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
class Axis_Admin_Customer_GroupController extends Axis_Admin_Controller_Back
{
    /**
     *
     * @var Axis_Account_Model_Customer_Group
     */
    private $_table;
    
    public function init()
    {
        parent::init();
        $this->_table =  Axis::single('account/customer_group');
    }
    
    public  function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Manage Customer Groups'
        );
        $this->render();
    }
    
    public function listAction()
    {
        $alpha = new Axis_Filter_DbField();
        
        $params = array();
        $params['sort']     = $alpha->filter($this->_getParam('sort', 'id'));
        $params['dir']     = $alpha->filter($this->_getParam('dir', 'DESC'));
        $params['where'] = 'id > 0';
        
        $dataset = $this->_table->getList($params);
        
        return $this->_helper->json->sendSuccess(array(
            'data' => $dataset
        ));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
            
        $this->_table->save($data);
            
        return $this->_helper->json->sendSuccess();
    }
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        
        $customerGroupIds = Zend_Json::decode($this->_getParam('data'));

        $isValid = true;
        $guestGroupId = Axis::single('account/customer_group')
            ->getIdByName('Guest');

        if (in_array($guestGroupId, $customerGroupIds)) {
            $isValid = false;
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Your can't delete default Guest group id: %s ",
                    $guestGroupId
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
            $this->_table->delete(
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
    
    public function getGroupsAction()
    {
        $this->_helper->layout->disableLayout();
        
        $this->_helper->json->sendSuccess(array(
           'data' => $this->_table->fetchAll()->toArray()
        ));
    }
}