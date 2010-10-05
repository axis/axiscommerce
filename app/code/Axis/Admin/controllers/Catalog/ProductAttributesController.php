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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Catalog_ProductAttributesController extends Axis_Admin_Controller_Back
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
        $dbField = new Axis_Filter_DbField();
        
        $order = $dbField->filter($this->_getParam('sort', 'id')) . ' '
               . $dbField->filter($this->_getParam('dir', 'ASC'));
        $start = (int) $this->_getParam('start', 0);
        $limit = (int) $this->_getParam('limit', 20);

        $select = Axis::single('catalog/product_option')
            ->select('*')
            ->calcFoundRows()
            ->order($order)
            ->limit($limit, $start)
            ->addNameAndDescription(Axis_Locale::getLanguageId())
            ;
//            ->joinLeft('catalog_product_option_text',
//                Axis::db()->quoteInto(
//                    'cpot.option_id = cpo.id AND cpot.language_id = ?',
//                    Axis_Locale::getLanguageId()
//                ),
//                array('name', 'description')
//            );
        
        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess();
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        return $this->_helper->json->sendJson(array(
            'success' => Axis::single('catalog/product_option')
                ->save($this->_getParam('option'))
        ));
    }
    
    public function getDataAction()
    {
        $this->_helper->layout->disableLayout();
        
        $id = $this->_getParam('id', 0);
        
        $result = array();
        
        if ($id && $row = Axis::single('catalog/product_option')->find($id)->current()) {
            $result = $row->toArray();
            $texts = $row->findDependentRowset('Axis_Catalog_Model_Product_Option_Text');
            foreach ($texts as $text) {
                $result['text']['lang_' . $text['language_id']]['name'] = $text['name'];
                $result['text']['lang_' . $text['language_id']]['description'] = $text['description'];
            }
        }
        
        $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
    }
    
    public function deleteAction()
    {
        Axis::single('catalog/product_option')->delete(
            $this->db->quoteInto('id IN(?)', 
            Zend_Json_Decoder::decode($this->_getParam('data'))
        ));
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Option was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
}