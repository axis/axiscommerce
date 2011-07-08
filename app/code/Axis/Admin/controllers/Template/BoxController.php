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
class Axis_Admin_Template_BoxController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $this->_helper->layout->disableLayout();

        $select = Axis::model('core/template_box')->select('*')
            ->calcFoundRows()
            ->addPageIds()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
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

        $row = Axis::model('core/template_box')
            ->getRow($data['box']);
        $row->save();

        $mBoxPage = Axis::model('core/template_box_page');
        $mBoxPage->delete(Axis::db()->quoteInto('box_id = ?', $row->id));

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
                $rowBoxPage = $mBoxPage->getRow($_rowData);
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
            ->setData(array('id' => $row->id))
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Zend_Json::decode($this->_getParam('data'));
        $modelBlock = Axis::single('core/template_box');
        $modelAssign = Axis::model('core/template_box_page');
        foreach ($data as $rowData) {
            if (!isset($rowData['id'])) {
                $rowData['config'] = '{}';
            }
            $row = $modelBlock->getRow($rowData);
            $row->save();

            $assigns = array_filter(explode(',', $rowData['page_ids']));
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

    public function editAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id');
        $box = Axis::model('core/template_box')
            ->find($id)
            ->current()
            ->toArray();

        list($namespace, $module, $name) = explode('_', $box['class']);
        $boxClass   = Axis::getClass($namespace . '_' . $module . '/' . $name, 'Box');
        $boxObject  = Axis::model($boxClass, array(
            'supress_init' => true
        ));

        $box['configuration_fields'] = $boxObject->getConfigurationFields();
        $box['configuration_values'] = $boxObject->getConfigurationValues();

        $select = Axis::model('core/template_box_page')
            ->select('*')
            ->where('box_id = ?', $id);

        $box['assignments'] = array();
        foreach ($select->fetchAll() as $assignment) {
            $box['assignments'][] = $assignment;
        }

        return $this->_helper->json->sendSuccess(array(
            'data' => $box
        ));
    }

    public function deleteAction()
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