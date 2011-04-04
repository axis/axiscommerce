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
        $select = Axis::model('discount/discount')->select('*')
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

        $displayMode = $this->_getParam('displayMode', 'without-special');
        if ('only-special' == $displayMode) {
            $select->addFilterBySpecial();
        } else if ('without-special' == $displayMode) {
            $select->addFilterByNonSpecial();
        }

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    private function _initForm()
    {
        $this->view->sites = Axis_Collect_Site::collect();
        $this->view->customerGroups = Axis_Collect_CustomerGroup::collect();
        $this->view->manufactures = Axis_Collect_Manufacturer::collect();
        $languageId = Axis_Locale::getLanguageId();

        $this->view->categoryTrees = Axis::single('catalog/category')->select('*')
            ->addName($languageId)
            ->addKeyWord()
            ->order('cc.lft')
            ->fetchAllAndSortByColumn('site_id');
        
        $this->view->attributes = Axis::single('catalog/product_option')
            ->getValueSets($languageId);
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
        $this->_helper->layout->disableLayout();
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
        $this->_helper->layout->disableLayout();

        $ids = Zend_Json::decode($this->_getParam('data'));
        $mDiscount = Axis::model('discount/discount');

        $discounts = $mDiscount->find($ids);
        foreach ($discounts as $discount) {
            $discountData = $discount->toArray();
            $discountData['products'] = $discount->getApplicableProducts();

            $discount->delete();

            Axis::dispatch('discount_delete_after', array(
                'discount_data' => $discountData
            ));
        }

        $this->_helper->json->sendJson(array(
            'success' => true
        ));
    }
}