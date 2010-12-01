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
        
        return $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('catalog/product_option_ValueSet')->fetchAll()->toArray()
        ));
    }
    
    public function saveSetAction()
    {
        $this->_helper->layout->disableLayout();
        
        return $this->_helper->json->sendJson(array(
            'success' => Axis::single('catalog/product_option_ValueSet')
                ->save(array($this->_getAllParams()))
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
        $setId = $this->_getParam('setId', 0);
        
        $rows = Axis::single('catalog/product_option_value')
            ->fetchAll($this->db->quoteInto('valueset_id = ?', $setId));
        
        $values = array();
        foreach ($rows as $row) {
            $values[$row->id] = $row->toArray();
        }
        
        if (sizeof($values)) {
            $rows = Axis::single('catalog/product_option_value_text')
                ->fetchAll($this->db->quoteInto('option_value_id IN(?)',
                    array_keys($values))
                );
            foreach ($rows as $row) {
                $values[$row->option_value_id]['name_' . $row->language_id]
                    = $row->name;
            }
        }
        
        return $this->_helper->json->sendSuccess(array(
            'data' => array_values($values)
        ));
    }
    
    public function saveValuesAction()
    {
        $this->_helper->layout->disableLayout();
        
        return $this->_helper->json->sendJson(array(
            'success' => Axis::single('catalog/product_option_value')
                ->save(Zend_Json::decode($this->_getParam('data')))
        ));
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