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
class Axis_Admin_Tax_RateController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->zones = Axis::single('location/geozone')
            ->fetchAll()->toArray();
        $this->view->taxClasses = Axis::single('tax/class')
            ->fetchAll()->toArray();
        $this->view->customerGroups = Axis::single('account/customer_group')
            ->fetchAll()->toArray();
        $this->view->pageTitle = Axis::translate('tax')->__('Tax Rates');
        $this->render();
    }

    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        
        $dbField = new Axis_Filter_DbField();
        $order = $dbField->filter($this->_getParam('sort', 'id')) . ' '
               . $dbField->filter($this->_getParam('dir', 'ASC'));

        $limit = (int) $this->_getParam('limit', 20);
        $start = $this->_getParam('start', 0);

        $select = Axis::single('tax/rate')
            ->select()
            ->calcFoundRows()
            ->order($order)
            ->limit($limit, $start)
            ;

        $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess();
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($data)) {
            return;
        }
        // Saving exists values
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('tax/rate')->save($data)
        ));
    }
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!count($ids)) {
            return;
        }
        Axis::single('tax/rate')->delete($this->db->quoteInto('id IN(?)', $ids));
        $this->_helper->json->sendSuccess();
    }
}