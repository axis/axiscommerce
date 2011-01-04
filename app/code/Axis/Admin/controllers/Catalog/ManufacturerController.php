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
        $select = Axis::model('catalog/product_manufacturer')->select('id')
            ->calcFoundRows()
            ->distinct()
            ->addUrl()
            ->joinDescription()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            );

        $sort = $this->_getParam('sort', 'cpm.id');
        if (strstr($sort, 'cpmd.title_')) {
            $sort = 'cpmd.title';
        }
        $select->order($sort . ' ' . $this->_getParam('dir', 'DESC'));

        if (!$ids = $select->fetchCol()) {
            return $this->_helper->json->sendSuccess(array(
                'count' => 0,
                'data'  => array()
            ));
        }

        $count = $select->foundRows();

        $select = Axis::model('catalog/product_manufacturer')->select('*')
            ->addUrl()
            ->addDescription(false)
            ->where('cpm.id IN (?)', $ids)
            ->order($sort . ' ' . $this->_getParam('dir', 'DESC'));

        $result = array();
        foreach ($select->fetchAll() as $manufacturer) {
            $langId = $manufacturer['language_id'];
            if (!isset($result[$manufacturer['id']])) {
                $result[$manufacturer['id']] = $manufacturer;
                $result[$manufacturer['id']]['description'] = array();
                unset($result[$manufacturer['id']]['title']);
                unset($result[$manufacturer['id']]['language_id']);
                unset($result[$manufacturer['id']]['manufacturer_id']);
            }
            $result[$manufacturer['id']]['description']['lang_' . $langId] = array(
                'title'         => $manufacturer['title'],
                'description'   => $manufacturer['description']
            );
        }

        return $this->_helper->json->sendSuccess(array(
            'data'  => array_values($result),
            'count' => $count
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

        try {
            $id = Axis::model('catalog/product_manufacturer')
                ->save($this->_getAllParams());
            Axis::message()->addSuccess(
                Axis::translate('catalog')->__('Data was successfully saved')
            );
        } catch (Axis_Exception $e) {
            Axis::message()->addError($e->getMessage());
            return $this->_helper->json->sendFailure();
        }

        $this->_helper->json->sendJson(array(
            'success' => (bool) $id,
            'data'    => array(
                'id' => $id
            )
        ));
    }

    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();

        $mManufacturer = Axis::model('catalog/product_manufacturer');
        foreach (Zend_Json_Decoder::decode($this->_getParam('data')) as $data) {
            $mManufacturer->save($data);
        }

        $this->_helper->json->sendJson(array(
            'success' => true
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