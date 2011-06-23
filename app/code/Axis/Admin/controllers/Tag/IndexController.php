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
class Axis_Admin_Tag_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('tag')->__('Tags');
        if ($this->_hasParam('tagId')) {
            $this->view->tagId = $this->_getParam('tagId');
        }
        $this->render();
    }

    public function listAction()
    {
        $select = Axis::model('tag/customer')->select('*');
        $data = $select->distinct()
            ->calcFoundRows()
            ->joinLeft('tag_product', 'tp.customer_tag_id = tc.id', 'product_id')
            ->addCustomerData()
            ->addProductDescription()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 20),
                $this->_getParam('start', 0)
            )
            ->order($this->_getParam('sort', 'id') . ' ' . $this->_getParam('dir', 'DESC'))
            ->fetchAll();

        $this->_helper->json->sendSuccess(array(
            'count' => $select->foundRows(),
            'data'  => $data
        ));
    }

    public function deleteAction()
    {
        $this->layout->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            return;
        }

        $this->_helper->json->sendJson(array(
            'success' => (bool) Axis::model('tag/customer')->delete(
                $this->db->quoteInto('id IN(?)', $data)
            )
        ));
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!$data) {
            return $this->_helper->json->sendFailure();
        }
        $model = Axis::model('tag/customer');
        foreach ($data as $_row) {
            $row = $model->getRow($_row);
            $row->save();
        }

        $this->_helper->json->sendSuccess();
    }
}