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
class Admin_ConfigValueController extends Axis_Admin_Controller_Back
{
    private function _optionsToArray($option)
    {
        if (($data = json_decode($option, true))&& $data != $option) {
            return $data;
        }

        $data = explode(Axis_Config::MULTI_SEPARATOR, $option);

        $confData = array();
        foreach ($data as $value) {
            $value = trim($value);
            $confData[$value] = $value;
        }
        return $confData;
    }

    private function _collect($collectName, $assigned = '', $notUseFullClassName = true)
    {
        if ($notUseFullClassName)
             $collectName = 'Axis_Collect_' . $collectName;
        if (!empty($assigned)) {
            list($sec, $subsec, $key) = explode('/', $assigned);
            if (isset(Axis::config()->$sec->$subsec->$key)) {
                $parameter = Axis::config()->$sec->$subsec->$key;
                $values = call_user_func(array($collectName, 'collect'), $parameter);
            }
        } else {
            $values = call_user_func(array($collectName, 'collect'));
        }

        return $values;
    }

    private function _getHtml($handlerName, $param)
    {
        return call_user_func(
            array('Axis_Config_Handler_' . $handlerName, 'getHtml'), $param, $this->view
        );
    }

    private function _getName($collectName, $id)
    {
        return call_user_func(
            array('Axis_Collect_' . $collectName, 'getName'), $id
        );
    }

    private function _getSaveValue(
        $handlerName, $params = array(), $additional = array())
    {
        return call_user_func(
            array('Axis_Config_Handler_' . $handlerName, 'getSaveValue'), $params
        );
    }

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Configuration');
        $this->view->sites = Axis_Collect_Site::collect();
        $this->render();
    }

    public function listAction()
    {
        $model = Axis::model('core/config_field');
        $select = $model->select('id')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->where('lvl = 3')
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(array(
                $this->_getParam('sort', 'path')
                . ' '
                . $this->_getParam('dir', 'ASC')
            ));

        $ids    = $select->fetchCol();
        $count  = $select->foundRows();
        $_data   = array();

        if (count($ids)) {
            $select = $model->select('*')
                ->calcFoundRows()
                ->addValue()
                ->where('ccf.id IN (?)', $ids)
                ->order(array(
                    $this->_getParam('sort', 'path')
                    . ' '
                    . $this->_getParam('dir', 'ASC'),
                    'site_id DESC'
                ));

            if ($this->_hasParam('site_id')) {
                $select->where('ccv.site_id IN (?)', array(0, $this->_getParam('site_id')));
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
            $data[$row->id] = $row->toArray();
            $data[$row->id]['title'] =
                Axis::translate($row->getTranslationModule())->__($row->title);

            if (null != $row->config_options) {
                $data[$row->id]['config_options'] = $this->_optionsToArray($row->config_options);
            }

            if ('bool' == $row->config_type) {
                $_value = $row->value ? 'Yes' : 'No' ;
            } elseif ('handler' == $row->config_type && 'Crypt' == $row->model) {
                $_value = '****************';
            } elseif ('handler' !== $row->config_type && !empty($row->model)) {
                $_value = $this->_getName($row->model, $row->value);
            } else if (in_array($row->config_type, array('select', 'multiple'))
                && isset($data[$row->id]['config_options'][$row->value])) {

                $_value = $data[$row->id]['config_options'][$row->value];
            } else {
                $_value = $row->value;
            }

            $data[$row->id]['value'] = $_value;
            $data[$row->id]['from'] = $row->site_id ? 'site' : 'global';
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

        $row = Axis::single('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();

        $translator = Axis::translate($row->getTranslationModule());
//        $this->view->confField = $row->toArray();
        $row->description = $translator->__($row->description);
        $row->title = $translator->__($row->title);

        $this->view->confValue = Axis::single('core/config_value')
            ->getValue($path, $siteId);

        $this->view->siteId = $siteId;
        $this->view->configPath = $path;

        if (!empty($row->model)) {
            if ($row->config_type != 'handler') {
                $row->config_options =
                    $this->_collect($row->model, $row->model_assigned_with);
            } else {
                $row->config_options = $this->_getHtml(
                    $row->model,
                    Axis::single('core/config_value')->getValue($path, $siteId)
                );
            }

            $this->view->confValue = $this->_optionsToArray($this->view->confValue);
        } elseif ($row->config_type == 'select'
            || $row->config_type == 'multiple') {

            $row->config_options = $this->_optionsToArray($row->config_options);

            $this->view->confValue = $this->_optionsToArray($this->view->confValue);
        }
        $this->view->confField = $row->toArray();

        $this->render();
    }

    public function saveAction()
    {
        $path   = $this->_getParam('path');
        $siteId = $this->_getParam('siteId');
        $value  = $this->_getParam('confValue', '');
        $model  = Axis::model('core/config_value');

        $rowField = Axis::single('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();

        if ($rowField->config_type === 'handler') {

            //@todo kostul
            if (in_array($rowField->model, array('ShippingTableRateImport', 'ShippingTableRateExport'))) {
                $value = array($value, 'siteId' => $siteId);
            }

            $value = $this->_getSaveValue($rowField->model, $value, array());
        } elseif (is_array($value)) {
            $value = implode(Axis_Config::MULTI_SEPARATOR, $value);
        }

        $row = $model->select()
            ->where('path = ?', $path)
            ->where('site_id = ?', $siteId)
            ->fetchRow();
        /*
         * if such row not founded then create new record
         * It possible when we redeclare global config-value for site
         */
        if (!$row) {
            $row = $model->createRow(array(
                'config_field_id' => $rowField->id,
                'path'            => $path,
                'site_id'         => $siteId
            ));
        }
        $row->value = $value;
        $row->save();

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