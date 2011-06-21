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
class Axis_Admin_Catalog_ProductOptionValuesetController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('catalog')->__('Value Sets');
        $this->view->languages = Axis_Collect_Language::collect();
        $this->render();
    }

    public function listSetsAction()
    {
        $this->_helper->layout->disableLayout();

        $select = Axis::model('catalog/product_option_ValueSet')->select('*')
            ->addFilters($this->_getParam('filter', array()));

        return $this->_helper->json->sendSuccess(array(
            'data' => $select->fetchAll()
        ));
    }

    public function saveSetAction()
    {
        $this->_helper->layout->disableLayout();

        $rowId = Axis::single('catalog/product_option_ValueSet')
            ->save(array($this->_getAllParams()));

        return $this->_helper->json->sendSuccess(array(
            'data' => array(
                'id' => $rowId
            )
        ));
    }

    public function deleteSetsAction()
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
        Axis::single('catalog/product_option_ValueSet')->delete(
            $this->db->quoteInto('id IN(?)', $ids)
        );
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Value Set was deleted sucessfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function listValuesAction()
    {
        $this->_helper->layout->disableLayout();

        $filters = $this->_getParam('filter', array());
        $select = Axis::model('catalog/product_option_value')->select('*')
            ->joinLeft('catalog_product_option_value_text',
                'cpov.id = cpovt.option_value_id',
                array('language_id', 'name'))
            ->addFilters($filters)
            ->where('cpov.valueset_id = ?', $this->_getParam('setId', 0));

        $result = array();
        foreach ($select->fetchAll() as $row) {
            if (!isset($result[$row['id']])) {
                $result[$row['id']] = $row;
                unset($result[$row['id']]['name']);
            }
            $result[$row['id']]['name_' . $row['language_id']] = $row['name'];
        }

        if (count($result)) {
            foreach ($filters as $filter) {
                if ('name' != $filter['field']) {
                    continue;
                }
                $values = Axis::model('catalog/product_option_value_text')->select('*')
                    ->where('cpovt.option_value_id IN (?)', array_keys($result))
                    ->fetchAll();

                foreach ($values as $value) {
                    $result[$value['option_value_id']]['name_' . $value['language_id']] = $value['name'];
                }

                break;
            }
        }

        return $this->_helper->json->sendSuccess(array(
            'data' => array_values($result)
        ));
    }

    public function saveValuesAction()
    {
        $this->_helper->layout->disableLayout();
        
        $dataset     = Zend_Json::decode($this->_getParam('data'));
        $model       = Axis::model('catalog/product_option_value');
        $modelLabel  = Axis::single('catalog/product_option_value_text');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        foreach ($dataset as $_row) {
            $row = $model->getRow($_row);
            $row->save();
            foreach ($languageIds as $languageId) {
                $rowLabel = $modelLabel->getRow($row->id, $languageId);
                $rowLabel->name = $_row['name_' . $languageId];
                $rowLabel->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function deleteValuesAction()
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

        Axis::single('catalog/product_option_value')->delete(
            $this->db->quoteInto('id IN(?)', $ids)
        );

        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Value was deleted sucessfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}