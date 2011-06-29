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
class Axis_Admin_Sales_OrderStatusController extends Axis_Admin_Controller_Back
{
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

        $model = Axis::single('sales/order_status');
        if (null === $parentId && $this->_hasParam('statusId')) {
            $statusId = (int) $this->_getParam('statusId');
            if (!$model->getSystem($statusId)) {
                $parentId = current(Axis::single('sales/order_status_relation')
                    ->getParents($statusId)
                );
            } else {
                $parentId = $statusId;
            }
        }

        $data = $model->getList($parentId);
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

        $dataset = Zend_Json::decode($this->_getParam('data'));

        if (!sizeof($dataset)) {
            return;
        }
        
        $model       = Axis::model('sales/order_status');
        $modelLabel  = Axis::model('sales/order_status_text');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        foreach ($dataset as $_row) {
            $row = $model->getRow($_row);
            $row->save();
            foreach ($languageIds as $languageId) {
                $rowLabel = $modelLabel->getRow($row->id, $languageId);
                $rowLabel->status_name = $_row['status_name_' . $languageId];
                $rowLabel->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                'Status was saved successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $_row = $this->_getAllParams();
        
        $model       = Axis::model('sales/order_status');
        $modelLabel  = Axis::model('sales/order_status_text');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        $row = $model->getRow($_row);
        $row->save();
        
        foreach ($languageIds as $languageId) {
            $rowLabel = $modelLabel->getRow($row->id, $languageId);
            $rowLabel->status_name = $_row['status_name'][$languageId];
            $rowLabel->save();
        }
        
        if (!$row->system) {
            $modelRelation = Axis::model('sales/order_status_relation');
            $modelRelation->delete(
                $this->db->quoteInto('from_status = ?', $row->id) .
                $this->db->quoteInto('OR to_status = ?', $row->id)
            );
            
            $from = $this->_getParam('from');
            $modelRelation->add($from, $row->id);
            
            $childrens = array_filter(explode(',', $this->_getParam('to'))); 
            foreach ($childrens as $child) {
                $modelRelation->add($row->id, (int) $child);
            }
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

        $mOrderStatus = Axis::single('sales/order_status');

        $systemIds = $mOrderStatus->select('id')
            ->where('system = 1')
            ->fetchCol();

        $ids = array_diff($ids, $systemIds);

        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Removing the system statuses are disalowed'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $mOrderStatus->delete($this->db->quoteInto('id IN (?)', $ids));

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

        $parents = $status->getParents();

        return $this->_helper->json->sendSuccess(array(
            'data' => array_merge(array(
                'from' => current($parents),
                'name' => $status->name,
                'statusId' => $status->id,
                'to' => implode(',', $status->getChildrens()),
            ), $translates)
        ));
    }
}
