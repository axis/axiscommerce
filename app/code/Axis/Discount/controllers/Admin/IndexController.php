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
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Discount
 * @subpackage  Axis_Discount_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Discount_Admin_IndexController extends Axis_Admin_Controller_Back
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

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
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
        
        
        $select = Axis::model('catalog/product_option_value')->select('*')
            ->joinLeft('catalog_product_option_value_text',
                'cpov.id = cpovt.option_value_id',
                'name')
            ->where('cpovt.language_id = ?', $languageId)
            ;
        $valuesetValues = array();
        foreach ($select->fetchAll() as $_row) {
            $valuesetValues[$_row['valueset_id']][$_row['id']] = $_row['name'];
        }
        $select = Axis::single('catalog/product_option')
                ->select('*')
                ->addNameAndDescription($languageId)
                ;
        $attributes = array();
        foreach ($select->fetchAll() as $_option) {
            if (isset($valuesetValues[$_option['valueset_id']])) {
                $attributes[$_option['id']] = array(
                    'name'   => $_option['name'],
                    'option' => $valuesetValues[$_option['valueset_id']]
                );
            }
        }
        
        $this->view->attributes = $attributes;
    }
    // @todo remove getCustomInfo, form-discount with childs(js also)
    public function loadAction()
    {
        $this->view->pageTitle = Axis::translate('discount')->__(
            'Edit Discount'
        );
        $this->_initForm();

        $discount = Axis::single('discount/discount')
            ->find($this->_getParam('id', 0))
            ->current();

        if (!$discount instanceof Axis_Db_Table_Row) {
            $this->_redirect('/discount/create');
        }
        $this->view->discount = $discount->getCustomInfo();
        $this->render('form-discount');
    }
    
    public function load1Action()
    {
        $discount = Axis::single('discount/discount')
            ->find($this->_getParam('id', 0))
            ->current();

//        $discount = $discount->getCustomInfo();
        $data = array('discount' => $discount->toArray());
        
        $data['eav'] = $discount->getRules();

//        if (isset($rules['conditions'])) {
//            $data['eav']['conditions'] = $rules['conditions'];
//        }
//        if (isset($rules['category'])) {
//            $data['eav']['category'] = $rules['category'];
//        }
//        if (isset($rules['productId'])) {
//            $data['eav']['productId'] = $rules['productId'];
//        }
//
//        if (isset($rules['manufacture'])) {
//            $data['eav']['manufacture'] = array_intersect(
//                $rules['manufacture'],
//                array_keys(Axis_Collect_Manufacturer::collect())
//            );
//        }
//        if (isset($rules['site'])) {
//            $data['eav']['site'] = array_intersect(
//                $rules['site'],
//                array_keys(Axis_Collect_Site::collect())
//            );
//        }
//        if (isset($rules['group'])) {
//            $data['eav']['group'] = array_intersect(
//                $rules['group'],
//                array_keys(Axis_Collect_CustomerGroup::collect())
//            );
//        }
//
//        if (isset($rules['special'])) {
//            $data['eav']['special'] = current($rules['special']);
//        }
//        if (isset($rules['optionId'])) {
//            $data['eav']['optionId'] = $rules['optionId'];
////            foreach ($rules['optionId'] as $optionId) {
////                if (!isset($rules['option[' . $optionId . ']'])) {
////                    continue;
////                }
////                foreach ($rules['option[' . $optionId . ']'] as $optionValueId) {
////                    $data['eav']['attributes'][] = array(
////                        'optionId' => $optionId,
////                        'optionValueId' => $optionValueId
////                    );
////                }
////            }
//
//        }
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
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
        $_row       = $this->_getParam('discount', array());
        $conditions = $this->_getParam('condition', array());
        
        $row = Axis::single('discount/discount')->save($_row);
        
        $model = Axis::model('discount/eav');
        $model->delete('discount_id = ' . $row->id);
        
        $sites = array();
        if (!empty($_row['site_ids'])) {
            $sites = $_row['site_ids'];
        }
        $groups = array();
        if (!empty($_row['customer_group_ids'])) {
            $groups = $_row['customer_group_ids'];
        }
        $special = false;
        if (!empty($_row['special'])) {
            $special = (bool) $_row['special'];
        }
        $row->setSites($sites)
            ->setCustomerGroups($groups)
            ->setSpecial($special)
            ->setConditions($conditions)
            ;
        Axis::message()->addSuccess(
            Axis::translate('discount')->__(
                "Discount '%s' successefull saved", $row->name
            )
        );

        return $this->_helper->json
            ->setId($row->id)
            ->sendSuccess()
        ;
    }

    public function removeAction()
    {
        $ids = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::model('discount/discount');

        $discounts = $model->find($ids);
        foreach ($discounts as $discount) {
            $discountData = $discount->toArray();
            $discountData['products'] = $discount->getApplicableProducts();

            $discount->delete();

            Axis::dispatch('discount_delete_after', array(
                'discount_data' => $discountData
            ));
        }

        return $this->_helper->json->sendSuccess();
    }
}