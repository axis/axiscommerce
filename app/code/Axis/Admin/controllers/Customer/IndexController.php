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
class Axis_Admin_Customer_IndexController extends Axis_Admin_Controller_Back
{
    public function batchSaveAction()
    {
        Axis_FirePhp::log($this->_getAllParams());
        $this->_helper->layout->disableLayout();

        $data = Zend_Json::decode($this->_getParam('data'));
        
        $model = Axis::single('account/customer');

        foreach ($data as $id => $_row) {
            if (!$this->_isEmailValid($_row['email'], $_row['site_id'], $id)) {
                continue;
            }
            unset($_row['password']);
            $row = $model->find($id)->current();
            $row->setFromArray($_row)
                ->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('Axis_Core')->__(
                '%d record(s) was saved successfully', count($data)
        ));

        return $this->_helper->json->sendSuccess();
    }

    public function saveCustomerAction()
    {
        Axis_FirePhp::log($this->_getAllParams());
//        return;
        $_row    = $this->_getParam('customer'); 
        $details = $this->_getParam('custom_fields', array());

        if (!$this->_isEmailValid(
                $_row['email'],  $_row['site_id'], $_row['id']
            )) {

            return $this->_helper->json->sendFailure();
        }
        $model = Axis::single('account/customer');
        $row = $model->find($_row['id'])->current(); 
        $event = false;
        if (!$row) {
            list($row, $password) = $model->create($_row);
            $event = true;
            
            Axis::message()->addSuccess(
                Axis::translate('Axis_Account')->__(
                    'Customer account was created successfully'
            ));
        } else {
            if (empty($_row['password'])) {
                unset($_row['password']);
            } else {
                $_row['password'] = md5($_row['password']);
            }
            $row->setFromArray($_row);
            $row->modified_at = Axis_Date::now()->toSQLString();
            $row->save();
            Axis::message()->addSuccess(
                Axis::translate('Axis_Core')->__(
                    'Data was saved successfully'
            ));
        }

        $row->setDetails($details);
        
        // address
        if ($this->_hasParam('address')) {
            $addresses = Zend_Json::decode($this->_getParam('address'));
            
            $modelAddress = Axis::single('account/customer_address');
            foreach ($addresses as $address) {
                if (!empty($address['id']) && $address['remove']) {
                    $modelAddress->delete(
                        Axis::db()->quoteInto('id = ?', $address['id'])
                    );
                } else {
                    $row->setAddress($address);
                }
            }
        }
        
        if ($event) {
            Axis::dispatch('account_customer_register_success', array(
                'customer' => $row,
                'password' => $password
            ));
        }    

        $this->_helper->json->sendSuccess(array(
            'data' => array('customer_id' => $row->id)
        ));
    }

    protected function _isEmailValid($email, $siteId, $customerId = null)
    {
        $where = Axis::db()->quoteInto('site_id = ?', $siteId);
        if (null !== $customerId) {
            $where .= Axis::db()->quoteInto(' AND id <> ?', $customerId);
        }
        $validator = new Axis_Validate_Exists(
            Axis::single('account/customer'),
            'email',
            $where
        );
        if (!$validator->isValid($email)) {
            foreach ($validator->getMessages() as $message) {
                Axis::message()->addError($message);
            }
            return false;
        }
        return true;
    }
    
    public function getAddressListAction()
    {
        $this->_helper->layout->disableLayout();
        $addresses = array();
        if ($customerId = (int)$this->_getParam('customerId')) {
            $rowset = Axis::single('account/customer_address')
                ->getSortListByCustomerId($customerId);

            foreach($rowset as $address) {
                $addresses[] = $address->toArray();
            }
        }

        return $this->_helper->json->setData($addresses)->sendSuccess();
    }
}