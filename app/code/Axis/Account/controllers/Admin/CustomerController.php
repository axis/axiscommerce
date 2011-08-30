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
class Axis_Account_Admin_CustomerController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Manage Customers'
        );

        if ($this->_hasParam('customerId')) {
            $this->view->customerId = $this->_getParam('customerId');
        }

        $this->view->userForm = array();
        $this->view->valueSet = array();

        $value = Axis::single('account/Customer_ValueSet_Value');

        $modelCustomerFields = Axis::single('account/customer_field');
        $fieldGroups = Axis::single('account/Customer_FieldGroup')
            ->getGroups(Axis_Locale::getLanguageId());

        foreach ($fieldGroups as $fieldGroup){
            //getting all fields
            $this->view->userForm[$fieldGroup['id']]['title'] = $fieldGroup['title'];
            $this->view->userForm[$fieldGroup['id']]['is_active'] =
                $fieldGroup['is_active'];
            $this->view->userForm[$fieldGroup['id']]['fields'] =
                $modelCustomerFields->getFieldsByGroup($fieldGroup['id']);
            //getting only used valuesets
            foreach ($this->view->userForm[$fieldGroup['id']]['fields'] as $fd){
                if (!isset($fd['customer_valueset_id'])) continue;
                $this->view->valueSet[$fd['customer_valueset_id']]['values'] =
                    $value->getValues($fd['customer_valueset_id']);
            }
        }
        $this->render();
    }
    
    public function listAction()
    {
        $select = Axis::model('account/customer')->select('*')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        //extjs combobox compatible
        if ($query = $this->_getParam('query')) {
            $query = '%' . $query . '%';
            $select->orWhere('ac.email LIKE ?', $query)
                ->orWhere('ac.firstname LIKE ?', $query)
                ->orWhere('ac.lastname LIKE ?', $query);
        }

        $accounts = $select->fetchAll();
        foreach ($accounts as &$account) {
            unset($account['password']);
        }

        return $this->_helper->json->sendSuccess(array(
            'data'  => $accounts,
            'count' => $select->foundRows()
        ));
    }
}