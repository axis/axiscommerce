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
class Axis_Admin_Discount_IndexController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('discount')->__(
            'Discounts'
        );
        $this->render();
    }

    public function listAction()
    {
        $alpha = new Axis_Filter_DbField();

        $params = array(
            'start' => (int) $this->_getParam('start', 0),
            'limit' => (int) $this->_getParam('limit', 25),
            'sort' => $alpha->filter($this->_getParam('sort', 'id')),
            'dir' => $alpha->filter($this->_getParam('dir', 'ASC'))
        );
        $displayMode = $this->_getParam('displayMode', 'without-special');
        switch ($displayMode) {
            case 'only-special':
                $params['special'] = 1;
                break;
            case 'without-special':
                $params['special'] = 0;
                break;
            default:
                break;
        }

        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('discount/discount')->getList($params),
            'count' => Axis::single('discount/discount')->getCount($params)
        ));
    }

    private function _initForm()
    {
        $this->view->sites = Axis_Collect_Site::collect();
        $this->view->customerGroups = Axis_Collect_CustomerGroup::collect();
        $this->view->manufactures = Axis_Collect_Manufacturer::collect();
        $this->view->categoryTrees = Axis::single('catalog/category')
            ->getFlatTree($this->_langId);
        $this->view->attributes = Axis::single('catalog/product_option')
            ->getValueSets($this->_langId);
    }

    public function editAction()
    {
        $this->view->pageTitle = Axis::translate('discount')->__(
            'Edit Discount'
        );
        $this->_initForm();

        $discount = Axis::single('discount/discount')
            ->find($this->_getParam('id', 0))
            ->current();
            
        if (!$discount instanceof Axis_Db_Table_Row) {
            $this->_redirect('/discount_index/create');
        }
        $this->view->discount = $discount->getCustomInfo();
        $this->render('form-discount');
    }

    public function createAction()
    {
        $this->view->pageTitle = Axis::translate('discount')->__(
            'Add New Discount'
        );
        $this->_initForm();
        $this->render('form-discount');
    }

    public function saveAction()
    {
        $this->layout->disableLayout();
        $params = $this->_getAllParams();
        $discountId = Axis::single('discount/discount')->save($params);

        Axis::message()->addSuccess(
            Axis::translate('discount')->__(
                "Discount '%s' successefull saved", $params['discountName']
            )
        );
        $this->_helper->json->sendSuccess(array(
            'id' => $discountId
        ));
    }

    public function deleteAction()
    {
        $this->layout->disableLayout();
        $discountIds = Zend_Json::decode($this->_getParam('data'));
        if (!sizeof($discountIds)) {
            return;
        }

        $this->_helper->json->sendJson(array(
            'success' => (bool) Axis::single('discount/discount')->delete(
                $this->db->quoteInto('id IN(?)', $discountIds)
            )
        ));
    }
}