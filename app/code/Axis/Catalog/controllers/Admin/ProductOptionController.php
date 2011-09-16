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
class Axis_Catalog_Admin_ProductOptionController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('catalog')->__(
            'Product Attributes'
        );
        $this->render();
    }

    public function listAction()
    {
        $order  = $this->_getParam('sort', 'id') . ' '
                . $this->_getParam('dir', 'ASC');
        $start  = (int) $this->_getParam('start', 0);
        $limit  = (int) $this->_getParam('limit', 20);
        $filter = $this->_getParam('filter', array());

        $select = Axis::model('catalog/product_option')
            ->select('*')
            ->calcFoundRows()
            ->order($order)
            ->limit($limit, $start)
            ->addFilters($filter)
            ->addNameAndDescription(Axis_Locale::getLanguageId());

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        $rowset = Axis::model('catalog/product_option')
            ->select(array('cpot.language_id', '*'))
            ->joinLeft(
                'catalog_product_option_text',
                'cpot.option_id = cpo.id',
                '*'
            )
            ->where('cpo.id = ?', $this->_getParam('id', 0))
            ->fetchAssoc();

        if (!$rowset) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Option %s not found', $this->_getParam('id', 0)
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $data = current($rowset);
        foreach (array_keys(Axis_Collect_Language::collect()) as $languageId) {
            if (!isset($rowset[$languageId]['name'])) {
                $rowset[$languageId]['name'] = '';
                $rowset[$languageId]['description'] = '';
            }
            $data['text']['lang_' . $languageId] = array(
                'name'        => $rowset[$languageId]['name'],
                'description' => $rowset[$languageId]['description']
            );
        }

        $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $data = $this->_getParam('option');
        $row = Axis::single('catalog/product_option')->save($data);

        $mText = Axis::model('catalog/product_option_text');
        foreach ($data['text'] as $languageId => $values) {
            $rowText = $mText->getRow($row->id, $languageId);
            $rowText->setFromArray($values)->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Option was saved successfully'
            )
        );
        return $this->_helper->json
            ->setData(array(
                'id' => $row->id
            ))
            ->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('catalog/product_option')->delete(
            $this->db->quoteInto('id IN (?)', $data
        ));
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Option was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function getFormAction()
    {
        $this->_helper->layout->disableLayout();
        $productId = $this->_getParam('productId');
        /**
         * @var $product Axis_Catalog_Model_Product_Row
         */
        $product = Axis::single('catalog/product')
            ->find($productId)
            ->current();
        if (!$product instanceof Axis_Catalog_Model_Product_Row) {
           return $this->_helper->json->sendFalure();
        }
        $data['properties']  = $product->getProperties();
        $data['modifiers']   = $product->getModifiers();
        $variations = $product->getVariationAttributesData();
        foreach ($variations as $key => $value) {
            $data[$key] = $value;
        }
        $data['price'] = $product->getPriceRules();
        $this->view->product = $data;
        $formHtml = $this->view->render('admin/catalog/product-attributes/get-form.phtml');
        return $this->_helper->json
           ->setData(array('form' => $formHtml, 'variations' => $variations))
           ->sendSuccess();
    }

    public function nlistAction()
    {
        $id = $this->_getParam('node', 0); // option_id
        $data = array();

        $modelProductOption = Axis::single('catalog/product_option');
        $leafOptions = array(
            Axis_Catalog_Model_Product_Option::TYPE_STRING,
            Axis_Catalog_Model_Product_Option::TYPE_TEXTAREA,
            Axis_Catalog_Model_Product_Option::TYPE_FILE
        );
        if (!$id) {
            // return options list
            $options = $modelProductOption
                ->select('*')
                ->calcFoundRows()
                ->addNameAndDescription(Axis_Locale::getLanguageId())
                ->fetchAll();

            foreach ($options as $item) {
                $data[] = array(
                   'text'        => $item['name'],
                   'code'        => $item['code'],
                   'option_name' => $item['name'],
                   'id'          => $item['id'],
                   'option_id'   => $item['id'],
                   'parent'      => null,
                   'leaf'        => in_array($item['input_type'], $leafOptions) ? true : false,
                   'input_type'  => $item['input_type'],
                   'languagable' => $item['languagable']
                );
            }
        } else {
            /**
             * @var Axis_Catalog_Model_Product_Option_Row
             */
            $option = $modelProductOption->find($id)->current();

            $languageId = Axis_Locale::getLanguageId();
            $optionText = $option->findDependentRowset(
                'Axis_Catalog_Model_Product_Option_Text',
                'Option',
                $modelProductOption->select()
                ->where('language_id = ?', $languageId)
            )->current();

            $values = $option->getValuesArrayByLanguage($languageId);

            foreach ($values as $value) {
                $data[] = array(
                    'text'        => $value['name'],
                    'option_name' => $optionText ? $optionText->name : $option->code,
                    'option_code' => $option->code,
                    'value_name'  => $value['name'],
                    'id'          => $id . '_' . $value['id'], // prevent conflicting with parent ids
                    'option_id'   => $id,
                    'parent'      => $id,
                    'value_id'    => $value['id'],
                    'input_type'  => -1,
                    'leaf'        => true
                );
            }
        }

        $this->_helper->json->sendRaw($data);
    }
}