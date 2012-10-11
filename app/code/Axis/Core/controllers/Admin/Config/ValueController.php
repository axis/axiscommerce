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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_Config_ValueController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Configuration');
        $this->render();
    }

    public function listAction()
    {
        $filters = $this->_getParam('filter', array());
        $limit   = $this->_getParam('limit', 25);
        $start   = $this->_getParam('start', 0);
        $order   = $this->_getParam('sort', 'path') . ' '
                 . $this->_getParam('dir', 'ASC');

        $model  = Axis::model('core/config_field');
        $select = $model->select('id')
            ->calcFoundRows()
            ->addFilters($filters)
            ->where('lvl = 3')
            ->limit($limit, $start)
            ->order($order);

        $ids    = $select->fetchCol();
        $count  = $select->foundRows();
        $_data  = array();

        if (count($ids)) {
            $select = $model->select('*')
//                ->calcFoundRows()
                ->addValue()
                ->where('ccf.id IN (?)', $ids)
                ->order(array(
                    $order,
                    'site_id DESC'
                ));

            if ($this->_hasParam('site_id')) {
                $siteId = $this->_getParam('site_id');
                $select->where('ccv.site_id IN (?)', array(0, $siteId));
            }

            $_data = $select->fetchAll();
        }

        $rowset = new Axis_Db_Table_Rowset(array(
            'table'    => $model,
            'data'     => $_data,
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $model->getRowClass(),
            'stored'   => true
        ));

        $data = array();
        foreach ($rowset as $row) {
            if (isset($data[$row->id])) {
                continue;
            }

            $value = $row->value;
            if (!empty($row->model)) {
                $class = Axis::getClass($row->model);
                if (class_exists($class)
                    && in_array('Axis_Config_Option_Array_Abstract', class_parents($class))) {

                    $_model = Axis::single($row->model);

                    if (in_array('Axis_Config_Option_Encodable_Interface', class_implements($class))) {
                        $value = $_model->decode($value);
                    }

                    if (!is_array($value)) {
                        $value = array($value);
                    }

                    foreach ($value as  &$_value) {
                        $_value = isset($_model[$_value]) ?
                            $_model[$_value] : $_value;
                    }

                    $value = implode(', ', $value);
                } elseif ('Axis_Core_Model_Option_Crypt' === $class) {
                    $value = str_repeat('*', strlen($value));
                }
            }

            $translator = Axis::translate($row->getTranslationModule());
            $data[$row->id] = array_merge($row->toArray(), array(
                'title' => $translator->__($row->title),
                'value' => $value,
                'from'  => $row->site_id ? 'site' : 'global'
            ));
        }

        return $this->_helper->json
            ->setData(array_values($data))
            ->setCount($count)
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        $this->_helper->layout->disableLayout();
        $siteId = $this->_getParam('siteId');
        $path = $this->_getParam('path');

        $this->view->siteId = $siteId;
        $this->view->configPath = $path;

        $row = Axis::model('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();

        $translator = Axis::translate($row->getTranslationModule());
        $row->description = $translator->__($row->description);
        $row->title = $translator->__($row->title);

        $value = Axis::config($path, $siteId);
        if ($value instanceof Axis_Config) {
            $value = $value->toArray();
        }
        if (!empty($row->model)) {
            $class = Axis::getClass($row->model);
            if (class_exists($class)
                && in_array('Axis_Config_Option_Array_Abstract', class_parents($class))) {

                $this->view->options = Axis::model($row->model)->toArray();
            }
        }
        $this->view->value = $value;

        $this->view->row = $row;
        $this->render();
    }

    public function saveAction()
    {
        $path   = $this->_getParam('path');
        $siteId = $this->_getParam('siteId');

        $value  = $this->_getParam('value');

        $rowField = Axis::single('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();

        $model = Axis::model('core/config_value');
        $row = $model->select()
            ->where('path = ?', $path)
            ->where('site_id = ?', $siteId)
            ->fetchRow();
        /*
         * if such row not founded then create new record
         * It possible when we redeclare global config_value for site
         */
        if (!$row) {
            $row = $model->createRow(array(
                'config_field_id' => $rowField->id,
                'path'            => $path,
                'site_id'         => $siteId
            ));
        }
        if (!empty($rowField->model)) {
            $class = Axis::getClass($rowField->model);
            if (class_exists($class)
                && in_array('Axis_Config_Option_Encodable_Interface', class_implements($class))) {

                $value = Axis::model($rowField->model)->encode($value);
            }
        }
        $row->value = $value;
        $row->save();

        Axis::dispatch('config_option_value_row_save_success', $row);

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Configuration option was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function useGlobalAction()
    {
        $siteId = (int) $this->_getParam('siteId');
        if (!$siteId) {
            return;
        }
        $pathItems = Zend_Json::decode($this->_getParam('pathItems'));
        $where = array($this->db->quoteInto('site_id = ?', $siteId));
        $model = Axis::single('core/config_value');

        foreach ($pathItems as $path) {
            $where[1] = $this->db->quoteInto('path = ?', $path);
            $model->delete($where);
        }
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Use global successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function copyGlobalAction()
    {
        $siteId = (int) $this->_getParam('siteId');
        if (!$siteId)
            return;

        $pathItems = Zend_Json::decode($this->_getParam('pathItems'));
        $where = array($this->db->quoteInto('site_id = ?', $siteId));
        $model = Axis::single('core/config_value');
        foreach ($pathItems as $path) {
            $where[1] = $this->db->quoteInto('path = ?', $path);

            $model->delete($where);
            $globalRow = $model->select()
                ->where('path = ?', $path)
                ->where('site_id = 0')
                ->fetchRow();

            if ($globalRow) {
                $model->createRow(array_merge($globalRow->toArray(), array(
                    'id'      => null,
                    'site_id' => $siteId
                )))->save();
            }
        }
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Copy global %s successfully', implode(',', $pathItems)
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}