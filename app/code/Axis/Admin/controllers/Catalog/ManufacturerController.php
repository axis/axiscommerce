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
class Axis_Admin_Catalog_ManufacturerController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('Axis_Catalog')->__('Product Brands');
        $this->render();
    }
    
    public function listAction()
    {
        $result = array();
        foreach (Axis::single('catalog/product_manufacturer')
                    ->getListBackend() as $manufacturer) {
            
            if (!isset($result[$manufacturer['id']])) {
                $result[$manufacturer['id']] = $manufacturer;
            }
            $result[$manufacturer['id']]['title_' . $manufacturer['language_id']] = $manufacturer['title'];
        }
        
        return $this->_helper->json->sendSuccess(array(
            'data' => array_values($result)
        ));
    }
    
    public function saveImageAction()
    {
        $this->_helper->layout->disableLayout();
        
        try {
            $uploader = new Axis_File_Uploader('image');
            $file = $uploader
                ->setAllowedExtensions(array('jpg','jpeg','gif','png'))
                ->setUseDispersion(true)
                ->save(Axis::config()->system->path . '/media/manufacturer');
            
            $result = array(
                'success' => true,
                'data' => array(
                    'path' => $file['path'],
                    'file' => $file['file']
                )
            );
        } catch (Axis_Exception $e) {
            $result = array(
                'success' => false,
                'messages' => array(
                    'error' => $e->getMessage()
                )
            );
        }
        
        return $this->getResponse()->appendBody(Zend_Json_Encoder::encode($result));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('catalog/product_manufacturer')->save(
                $this->_getParam('data')
             )
        ));
    }
    
    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('catalog/product_manufacturer')->save(
                Zend_Json_Decoder::decode($this->_getParam('data'))
             )
        ));
    }
   
    public function deleteAction()
    {
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Invalid data recieved'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('catalog/product_manufacturer')
                ->deleteByIds($ids)
        ));
    }
}