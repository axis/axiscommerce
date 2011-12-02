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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Admin_ProductOptionValuesetController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('catalog')->__('Value Sets');
        $this->view->languages = Axis_Locale_Model_Language::collect();
        $this->render();
    }

    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        
        $select = Axis::model('catalog/product_option_ValueSet')->select('*')
            ->addFilters($filter)
        ;

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->sendSuccess()
        ;
    }

    public function saveAction()
    {
        $_row = $this->_getAllParams();
        $row = Axis::model('catalog/product_option_ValueSet')->save($_row);

        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Data has been saved successfully'
            )
        );
        return $this->_helper->json
            ->setData($row->toArray())
            ->sendSuccess()
        ;
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
        Axis::single('catalog/product_option_ValueSet')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Value Set was deleted sucessfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}