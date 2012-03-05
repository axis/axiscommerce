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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Admin_OrderStatusController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('sales')->__('Order Status');
        $this->render();
    }

    public function listAction()
    {
        $_rowset = Axis::single('sales/order_status')->getList();
        $data = array();
        foreach ($_rowset as $_row) {
            if (empty($data[$_row['id']])) {
                $data[$_row['id']] = $_row;
            }
            $data[$_row['id']]['status_name_' . $_row['language_id']] = $_row['status_name'];
        }

        return $this->_helper->json
            ->setData(array_values($data))
            ->sendSuccess();
    }

    public function loadAction()
    {
        $statusId = $this->_getParam('statusId', false);
        $row = Axis::single('sales/order_status')
            ->find($statusId)
            ->current();

        if (!$row) {
            Axis::message()->addError(
                Axis::translate('sales')->__(
                    'Status not exist'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $_t = array();

        $statusText = Axis::single('sales/order_status_text')
                ->select(array('language_id', 'status_name'))
                ->where('status_id = ?', $statusId)
                ->fetchAssoc();

        foreach (array_keys(Axis_Locale_Model_Option_Language::getConfigOptionsArray()) as $languageId) {
            $_t['status_name[' . $languageId . ']'] = isset($statusText[$languageId]) ?
                $statusText[$languageId]['status_name'] : '';
        }

        $parents = $row->getParents();

        return $this->_helper->json->sendSuccess(array(
            'data' => array_merge(array(
                'from' => current($parents),
                'name' => $row->name,
                'statusId' => $row->id,
                'to' => implode(',', $row->getChildrens()),
            ), $_t)
        ));
    }

    public function saveAction()
    {
        $_row = $this->_getAllParams();

        $model       = Axis::model('sales/order_status');
        $modelLabel  = Axis::model('sales/order_status_text');
        $languageIds = array_keys(Axis_Locale_Model_Option_Language::getConfigOptionsArray());

        $row = $model->getRow($_row);
        $row->system = (int)$row->system;
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

    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));

        if (!sizeof($_rowset)) {
            return;
        }

        $model       = Axis::model('sales/order_status');
        $modelLabel  = Axis::model('sales/order_status_text');
        $languageIds = array_keys(Axis_Locale_Model_Option_Language::getConfigOptionsArray());

        foreach ($_rowset as $_row) {
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

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $model = Axis::single('sales/order_status');

        $systemIds = $model->select('id')
            ->where('system = 1')
            ->fetchCol();

        $data = array_diff($data, $systemIds);

        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Removing the system statuses are disalowed'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $model->delete($this->db->quoteInto('id IN (?)', $data));

        Axis::message()->addSuccess(
            Axis::translate('sales')->__(
                'Status was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    //@todo merge with list action
    public function getChildsAction()
    {
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

        $_rowset = $model->getList($parentId);
        $_data = array();
        foreach ($_rowset as $_row) {
            if (empty($_data[$_row['id']])) {
                $_data[$_row['id']] = $_row;
            }
            $_data[$_row['id']]['status_name_' . $_row['language_id']] = $_row['status_name'];
        }

        $data = array();
        if (isset($statusId) && $statusId) {
            foreach($_data as $id => $value) {
                if ($id == $statusId) {
                    continue;
                }
                $data[] = $value;
            }
        } else {
            $data = array_values($_data);
        }
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
    }
}
