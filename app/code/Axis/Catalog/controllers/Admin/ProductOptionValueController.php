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
class Axis_Catalog_Admin_ProductOptionValueController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $filters = $this->_getParam('filter', array());
        $valueSetId = $this->_getParam('setId', 0);
        
        $select = Axis::model('catalog/product_option_value')->select('*')
            ->joinLeft('catalog_product_option_value_text',
                'cpov.id = cpovt.option_value_id',
                array('language_id', 'name'))
            ->addFilters($filters)
            ->where('cpov.valueset_id = ?', $valueSetId)
        ;

        $data = array();
        foreach ($select->fetchAll() as $row) {
            if (!isset($data[$row['id']])) {
                $data[$row['id']] = $row;
                unset($data[$row['id']]['name']);
            }
            $data[$row['id']]['name_' . $row['language_id']] = $row['name'];
        }

        if (count($data)) {
            foreach ($filters as $filter) {
                if ('name' != $filter['field']) {
                    continue;
                }
                $values = Axis::model('catalog/product_option_value_text')->select('*')
                    ->where('cpovt.option_value_id IN (?)', array_keys($data))
                    ->fetchAll();

                foreach ($values as $value) {
                    $data[$value['option_value_id']]['name_' . $value['language_id']] = $value['name'];
                }

                break;
            }
        }

        return $this->_helper->json
            ->setData(array_values($data))
            ->sendSuccess()
        ;
    }

    public function batchSaveAction()
    {
        $_rowset     = Zend_Json::decode($this->_getParam('data'));
        $model       = Axis::model('catalog/product_option_value');
        $modelLabel  = Axis::single('catalog/product_option_value_text');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        foreach ($_rowset as $_row) {
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

        Axis::single('catalog/product_option_value')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Value was deleted sucessfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}