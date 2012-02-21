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
 * @package     Axis_Import
 * @subpackage  Axis_Import_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingTable
 * @subpackage  Axis_ShippingTable_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingTable_Admin_RateController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__("Shipping Table");
        $this->render();
    }
    
    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        $limit  = $this->_getParam('limit', 25); 
        $start  = $this->_getParam('start', 0);
        $order  = $this->_getParam('sort', 'id') . ' '
            . $this->_getParam('dir', 'DESC');

        $select = Axis::model('shippingTable/rate')->select('*')
            ->calcFoundRows()
            ->addFilters($filter)
            ->limit($limit, $start)
            ->order($order);
        
        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
    }
    
    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));

        if (!sizeof($_rowset)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $model = Axis::model('shippingTable/rate');
        foreach($_rowset as $_row) {
            $row = $model->getRow($_row);
            $row->save();
        }

        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction() 
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        Axis::model('shippingTable/rate')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        Axis::message()->addSuccess(
            Axis::translate('location')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}