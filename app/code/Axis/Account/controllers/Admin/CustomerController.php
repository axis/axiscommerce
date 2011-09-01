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

        $modelCustomerValueSetValue = Axis::model('account/Customer_ValueSet_Value');
        $modelCustomerFields = Axis::model('account/customer_field');
        
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
                    $modelCustomerValueSetValue->getValues($fd['customer_valueset_id']);
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
    
    public function loadAction()
    {
        if (!$customer = Axis::single('account/customer')->find((int)$this->_getParam('id'))->current()) {
            Axis::message()->addError(Axis::translate('Axis_Account')->__(
                "Customer '%s' not found", $this->_getParam('id')
            ));
            return $this->_helper->json->sendFailure();
        }

        $data = array();
        $data['customer'] = $customer->toArray();

        // custom fields
        $data['custom_fields'] = array();
        foreach ($customer->getDetails() as $item) {
            if (isset($data['custom_fields']['field_' . $item['customer_field_id']])) {
                $data['custom_fields']
                    ['field_' . $item['customer_field_id']] .= ','
                        . $item['customer_valueset_value_id'];
            } else {
                $data['custom_fields']
                    ['field_' . $item['customer_field_id']] = isset($item['data']) ?
                        $item['data'] : $item['customer_valueset_value_id'];
            }
        }

        // address
        $data['address'] = array();
        $rowset = Axis::single('account/customer_address')
            ->getSortListByCustomerId($customer->id);
        foreach($rowset as $address) {
            $data['address'][] = $address->toArray();
        }

        // orders
        $orders = Axis::single('sales/order')->fetchAll(
            $this->db->quoteInto('customer_id = ?', $customer->id)
        );
        $orderStatus = Axis_Collect_OrderStatus::collect();
        $orderStatusText = Axis_Collect_OrderStatusText::collect();

        $data['order'] = array();
        foreach ($orders as $order) {
            $data['order'] = $order->toArray();

            $data['order']['product'] = array_values($order->getProducts());
            foreach($data['order']['product'] as &$product) {
                $product['price'] =
                    $product['price'] * $order->currency_rate;
                $product['final_price'] =
                    $product['final_price'] * $order->currency_rate;
                $product['product_subtotal'] =
                    $product['final_price'] * $product['quantity'];
            }

            if (isset($orderStatusText[$order['order_status_id']])) {
                $data['order']['status'] = $orderStatusText[$order['order_status_id']];
            } else {
                $data['order']['status'] = isset($orderStatus[$order['order_status_id']]) ?
                    $orderStatus[$order['order_status_id']] : $order['order_status_id'];
            }
        }

        // shopping cart
        $data['shopping_cart'] = array();
        if ($cart = Axis::single('checkout/cart')->getCustomerCart($customer->id)) {
            $data['shopping_cart'] = array_values($cart->getProducts());
            foreach ($data['shopping_cart'] as &$product) {
                $product['attributes'] = array_values($product['attributes']);
            }
        }

        return $this->_helper->json->sendSuccess(array(
            'data' => $data
        ));
    }
    
    public function saveAction()
    {
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

        return $this->_helper->json->sendSuccess(array(
            'data' => array('customer_id' => $row->id)
        ));
    }
    
    public function batchSaveAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        
        $model = Axis::single('account/customer');

        foreach ($data as $_row) {
            if (!$this->_isEmailValid($_row['email'], $_row['site_id'], $_row['id'])) {
                continue;
            }
            unset($_row['password']);
            $row = $model->find($_row['id'])->current();
            $row->setFromArray($_row)
                ->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('Axis_Core')->__(
                '%d record(s) was saved successfully', count($data)
        ));

        return $this->_helper->json->sendSuccess();
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
    
    public function removeAction()
    {
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            return;
        }
        Axis::single('account/customer')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        return $this->_helper->json->sendSuccess();
    }
}