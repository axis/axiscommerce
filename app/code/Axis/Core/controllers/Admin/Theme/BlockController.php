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
class Admin_Theme_BlockController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        $limit  = $this->_getParam('limit', 25);
        $start  = $this->_getParam('start', 0);
        $order  = $this->_getParam('sort', 'id') . ' ' . $this->_getParam('dir', 'DESC');
        
        $select = Axis::model('core/template_box')->select('*')
            ->calcFoundRows()
            ->addPageIds()
            ->addFilters($filter)
            ->limit($limit, $start)
            ->order($order);

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess();
    }
    
    public function loadAction()
    {
        $id = $this->_getParam('id');
        $data = Axis::model('core/template_box')->find($id)->current()
            ->toArray();

        list($namespace, $module, $name) = explode('_', $data['class']);
        $boxClass   = Axis::getClass($namespace . '_' . $module . '/' . $name, 'Box');
        $boxObject  = Axis::model($boxClass, array(
            'supress_init' => true
        ));

        $data['configuration_fields'] = $boxObject->getConfigurationFields();
        $data['configuration_values'] = $boxObject->getConfigurationValues();

        $select = Axis::model('core/template_box_page')
            ->select('*')
            ->where('box_id = ?', $id);

        $data['assignments'] = array();
        foreach ($select->fetchAll() as $assignment) {
            $data['assignments'][] = $assignment;
        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }
    
    public function saveAction()
    {
        $data = $this->_getAllParams();

        $config = trim($data['additional_configuration']);
        if (!empty($config)) {
            $config = Zend_Json::decode($config);
        } else {
            $config = array();
        }
        $data['box']['config'] = Zend_Json::encode(
            array_merge($config, $data['configuration'])
        );

        $row = Axis::model('core/template_box')->getRow($data['box']);
        $row->save();

        $modelBoxToPage = Axis::model('core/template_box_page');
        $modelBoxToPage->delete(Axis::db()->quoteInto('box_id = ?', $row->id));

        if (isset($data['assignments'])) {
            $assignments = Zend_Json::decode($data['assignments']);
            foreach ($assignments as $_rowData) {
                if (!isset($_rowData['box_id']) && $_rowData['remove']) {
                    continue;
                }

                foreach ($_rowData as &$_columnValue) {
                    if ('' === $_columnValue) {
                        $_columnValue = null;
                    }
                }

                $_rowData['box_id'] = $row->id;
                $rowBoxPage = $modelBoxToPage->getRow($_rowData);
                if ($_rowData['remove']) {
                    $rowBoxPage->delete();
                } else {
                    $rowBoxPage->save();
                }
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__('Box was saved successfully')
        );
        return $this->_helper->json
            ->setData($row->toArray())
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $dataset = Zend_Json::decode($this->_getParam('data'));
        $model = Axis::single('core/template_box');
        $modelAssign = Axis::model('core/template_box_page');
        foreach ($dataset as $data) {
            if (!isset($data['id'])) {
                $data['config'] = '{}';
            }
            $row = $model->getRow($data);
            $row->save();

            $assigns = array_filter(explode(',', $data['page_ids']));
            $_where = array(
                Axis::db()->quoteInto('box_id = ?', $row->id)
            );
            if (count($assigns)) {
                $_where[] = Axis::db()->quoteInto('page_id NOT IN (?)', $assigns);
            }
            $modelAssign->delete($_where);
            foreach ($assigns as $pageId) {
                $_row = $modelAssign->find($row->id, $pageId)->current();
                if (!$_row) {
                    $_row = $modelAssign->createRow(array(
                        'box_id'   => $row->id,
                        'page_id'  => $pageId,
                        'box_show' => 1
                    ));
                }
                $_row->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__('Box was saved successfully')
        );

        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $ids = Zend_Json::decode($this->_getParam('data'));

        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        Axis::single('core/template_box')->delete(
            $this->db->quoteInto('id IN (?)', $ids)
        );

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}