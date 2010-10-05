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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Sales_OrderStatusController extends Axis_Admin_Controller_Back
{
    /**
     * Order status' model
     *
     * @var Axis_Sales_Model_Order_Status
     */
    protected $_table;
    
    public function init()
    {
        parent::init();
        $this->_table = Axis::single('sales/order_status_text');
    }
    
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('sales')->__('Order Status');
        $this->render();
    }
    
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Axis::single('sales/order_status')->getList();
        $result = array();
        foreach ($data as $row) {
            $result[$row['id']]['id'] = $row['id'];
            $result[$row['id']]['name'] = $row['name'];
            $result[$row['id']]['system'] = $row['system'];
            $result[$row['id']]['status_name_' . $row['language_id']] =
                $row['status_name'];
        }
        
        return $this->_helper->json->sendSuccess(array(
            'data'  => array_values($result)
        ));
    }
    
    public function getChildsAction()
    {
        $this->_helper->layout->disableLayout();
        
        $parentId = $this->_getParam('parentId', null);
        
        if (null === $parentId && $this->_hasParam('statusId')) {
            $statusId = intval($this->_getParam('statusId'));
            if (!Axis::single('sales/order_status')->getSystem($statusId)) { 
                $parentId = current(Axis::single('sales/order_status_relation')
                    ->getParents($statusId)
                );
            } else {
                $parentId = $statusId;
            }
        }
        
        $data = Axis::single('sales/order_status')->getList($parentId);
        $result = array();
        foreach ($data as $row) {
            $result[$row['id']]['id'] = $row['id'];
            $result[$row['id']]['name'] = $row['name'];
            $result[$row['id']]['system'] = $row['system'];
            $result[$row['id']]['status_name_' . $row['language_id']] =
                $row['status_name'];
        }
        
        $res = array(); 
        if (isset($statusId) && $statusId) {
            foreach($result as $id => $value) {
                if ($id == $statusId) {
                    continue;
                }
                $res[] = $value;
            }
        } else {
            $res = array_values($result);
        }
        return $this->_helper->json->sendSuccess(array(
            'data'  => $res
        ));
    }
    
    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $data = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($data)) {
            return;
        }    
        return $this->_helper->json->sendJson(array(
            'success' => Axis::single('sales/order_status')->batchSave($data)
        ));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $statusId = intval($this->_getParam('statusId', 0));
        if ($statusId) {
            $status = Axis::single('sales/order_status')->save(
                $statusId, 
                $this->_getParam('name'),
                intval($this->_getParam('from')),
                array_filter(explode(',', $this->_getParam('to'))),
                $this->_getParam('status_name')
            );
        } else {
            Axis::single('sales/order_status')->add(
                $this->_getParam('name'),
                intval($this->_getParam('from')),
                array_filter(explode(',', $this->_getParam('to'))),
                $this->_getParam('status_name')
            );
        }
        return $this->_helper->json->sendSuccess();
    } 
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        
        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
            
        Axis::single('sales/order_status')
            ->delete($this->db->quoteInto('id IN(?)', $ids));

        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                'Status was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function getInfoAction() 
    {
        $this->_helper->layout->disableLayout();
        $statusId = $this->_getParam('statusId', false);
        $status = Axis::single('sales/order_status')
            ->find($statusId)->current();
        
        if (!$status) {
            Axis::message()->addError(
                Axis::translate('sales')->__(
                    'Status not exist'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        
        $translates = array();
        
        $statusText = Axis::single('sales/order_status_text')
                ->select(array('language_id', 'status_name'))
                ->where('status_id = ?', $statusId)
                ->fetchAssoc();
        
        foreach (Axis_Collect_Language::collect() as $languageId => $language) {
            $translates['status_name[' . $languageId . ']'] = isset($statusText[$languageId]) ?
                $statusText[$languageId]['status_name'] : '';
        }
        
        return $this->_helper->json->sendSuccess(array(
            'data' => array_merge(array(
                'from' => current($status->getParents()),
                'name' => $status->name,
                'statusId' => $status->id,
                'to' => implode(',', $status->getChildrens()),
            ), $translates)
        ));
    }
}
