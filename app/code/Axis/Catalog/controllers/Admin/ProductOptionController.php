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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Admin_Controller
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
        foreach (array_keys(Axis::model('locale/option_language')->toArray()) as $languageId) {
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
        $formHtml = $this->view->render('catalog/product-option/get-form.phtml');
        return $this->_helper->json
           ->setData(array('form' => $formHtml, 'variations' => $variations))
           ->sendSuccess();
    }
    
    public function nlistAction()
    {
        $data  = array();
        
        $leafOptions = array(
            Axis_Catalog_Model_Product_Option::TYPE_STRING,
            Axis_Catalog_Model_Product_Option::TYPE_TEXTAREA,
            Axis_Catalog_Model_Product_Option::TYPE_FILE
        );
        $languageId = Axis_Locale::getLanguageId();
        
        $rowset = Axis::single('catalog/product_option')
            ->select('*')
            ->addNameAndDescription($languageId)
            ->fetchRowset();

        foreach ($rowset as $row) {
            $data[] = array(
                'id'          => $row->id,
                'text'        => $row->name,
                'parent'      => null,
                'leaf'        => in_array($row->input_type, $leafOptions) ? true : false,
                
                'option_id'   => $row->id,
                'option_name' => $row->name,
                'input_type'  => $row->input_type,
                
                'code'        => $row->code,
                'languagable' => $row->languagable
            );
        }
        
        $dataset = Axis::model('catalog/product_option_value')
            ->select(array('value_id' => 'id'))
            ->join('catalog_product_option_value_text', 
                'cpov.id = cpovt.option_value_id',
                array('value_name' => 'name')
            )
            ->join('catalog_product_option', 
                'cpo.valueset_id = cpov.valueset_id',
                array('option_code' => 'code')
            )
            ->join('catalog_product_option_text', 
                'cpo.id = cpot.option_id AND cpot.language_id = :languageId',
                array('option_id', 'option_name' => 'name') 
            )
            ->where('cpovt.language_id = :languageId')
            ->fetchAll(array(
                'languageId' => $languageId
            ))
            ;
        
        foreach ($dataset as $_data) {
            $data[] = array(
                'id'          => $_data['option_id'] . '_' . $_data['value_id'],
                'text'        => $_data['value_name'],
                'parent'      => $_data['option_id'],
                'leaf'        => true,
                
                'option_id'   => $_data['option_id'],
                'option_name' => $_data['option_name'],
                'input_type'  => -1,
                
                
                'option_code' => $_data['option_code'],
                'value_name'  => $_data['value_name'],
                'value_id'    => $_data['value_id']
                
            );
        }
        
        
        return $this->_helper->json->sendRaw($data);
    }
}