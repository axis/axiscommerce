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
class Axis_Catalog_Admin_ManufacturerController extends Axis_Admin_Controller_Back
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
            )
            ->order(
                $this->_getParam('sort', 'cpm.id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        if (!$ids = $select->fetchCol()) {
            return $this->_helper->json
                ->setData(array())
                ->setCount(0)
                ->sendSuccess()
            ;
        }

        $count = $select->foundRows();

        $select = Axis::model('catalog/product_manufacturer')->select('*')
            ->addUrl()
            ->addDescription(false)
            ->where('cpm.id IN (?)', $ids)
            ->order(
                $this->_getParam('sort', 'cpm.id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        $data = array();
        $languageIds = array_keys(Axis_Collect_Language::collect());
        foreach ($select->fetchAll() as $manufacturer) {
            if (!isset($data[$manufacturer['id']])) {
                $data[$manufacturer['id']] = $manufacturer;
                $data[$manufacturer['id']]['description'] = array();
                unset($data[$manufacturer['id']]['title']);
                unset($data[$manufacturer['id']]['language_id']);
                unset($data[$manufacturer['id']]['manufacturer_id']);
            }
            if ($languageId = $manufacturer['language_id']) {
                $data[$manufacturer['id']]['description']['lang_' . $languageId] = array(
                    'title'         => $manufacturer['title'],
                    'description'   => $manufacturer['description']
                );
            }
        }
        foreach ($languageIds as $languageId) {
            foreach ($data as $manufacturerId => &$values) {
                if (isset($values['description']['lang_' . $languageId])) {
                    continue;
                }
                $values['description']['lang_' . $languageId] = array(
                    'title'         => '',
                    'description'   => ''
                );
            }
        }


        return $this->_helper->json
            ->setData(array_values($data))
            ->setCount($count)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $data  = $this->_getAllParams();
        $model = Axis::model('catalog/product_manufacturer');
        try {
            $row = $model->save($data);
            $row->setDescriptions($data['description']);
            Axis::message()->addSuccess(
                Axis::translate('catalog')->__('Data was successfully saved')
            );
        } catch (Axis_Exception $e) {
            Axis::message()->addError($e->getMessage());
            return $this->_helper->json->sendFailure();
        }

        return $this->_helper->json
            ->setData(array('id' => $row->id))
            ->sendSuccess()
        ;
    }

    public function batchSaveAction()
    {
        $model   = Axis::model('catalog/product_manufacturer');
        $_rowset = Zend_Json::decode($this->_getParam('data'));
        $i       = 0;
        foreach ($_rowset as $_row) {
            try {
                $model->save($_row);
                $i++;
            } catch (Axis_Exception $e) {
                Axis::message()->addError($e->getMessage());
            }
        }

        if (!$i) {
            return $this->_helper->json->sendFailure();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__('%d record(s) was saved successfully', $i)
        );
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('catalog')->__(
                    'Invalid data recieved'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        Axis::single('catalog/product_manufacturer')->deleteByIds($data);

        return $this->_helper->json->sendSuccess();
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

            $data = array(
                'success' => true,
                'data' => array(
                    'path' => $file['path'],
                    'file' => $file['file']
                )
            );
        } catch (Axis_Exception $e) {
            $data = array(
                'success' => false,
                'messages' => array(
                    'error' => $e->getMessage()
                )
            );
        }

        return $this->getResponse()->appendBody(
            Zend_Json::encode($data)
        );
    }
}