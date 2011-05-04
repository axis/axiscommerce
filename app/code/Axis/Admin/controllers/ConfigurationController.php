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
class Axis_Admin_ConfigurationController extends Axis_Admin_Controller_Back
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

    public function getNodesAction()
    {
        $this->_helper->layout->disableLayout();
        $node = $this->_getParam('node', '0');

        $this->_helper->json->sendRaw(
            Axis::single('core/config_field')->getNodes($node)
        );
    }

    public function listAction()
    {
        $mConfigField = Axis::model('core/config_field');
        $select = $mConfigField->select('id')
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
        $data   = array();

        if (count($ids)) {
            $select = $mConfigField->select('*')
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

            $data = $select->fetchAll();
        }

        $rowset = new Axis_Db_Table_Rowset(array(
            'table'    => $mConfigField,
            'data'     => $data,
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $mConfigField->getRowClass(),
            'stored'   => true
        ));

        $values = array();
        foreach ($rowset as $row) {
            if (isset($values[$row->id])) {
                continue;
            }
            $values[$row->id] = $row->toArray();
            $values[$row->id]['title'] =
                Axis::translate($row->getTranslationModule())->__($row->title);

            if (null != $row->config_options) {
                $values[$row->id]['config_options'] = $this->_optionsToArray($row->config_options);
            }

            if ('bool' == $row->config_type) {
                $values[$row->id]['value'] = $row->value ? 'Yes' : 'No' ;
            } elseif ('handler' == $row->config_type && 'Crypt' == $row->model) {
                $values[$row->id]['value'] = '****************';
            } elseif ('handler' !== $row->config_type && !empty($row->model)) {
                $values[$row->id]['value'] = $this->_getName($row->model, $row->value);
            } else if (in_array($row->config_type, array('select', 'multiple'))
                && isset($values[$row->id]['config_options'][$row->value])) {

                $values[$row->id]['value'] = $values[$row->id]['config_options'][$row->value];
            } else {
                $values[$row->id]['value'] = $row->value;
            }

            $values[$row->id]['from'] = $row->site_id ? 'site' : 'global';
        }

        $this->_helper->json->sendSuccess(array(
            'data'  => array_values($values),
            'count' => $count
        ));
    }

    public function editAction()
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
        $this->_helper->layout->disableLayout();
        $path      = $this->_getParam('path');
        $siteId    = $this->_getParam('siteId');
        $confValue = $this->_getParam('confValue');

        $field = Axis::single('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();
        
        if ($field->config_type === 'handler') {

            //@todo kostul
            if (in_array($field->model, array('ShippingTableRateImport', 'ShippingTableRateExport'))) {
                $confValue = array($confValue, 'siteId' => $siteId);
            }

            $confValue = $this->_getSaveValue($field->model, $confValue, array());
        } elseif (is_array($confValue)) {
            $values = array();
            foreach ($confValue as $valueItem) {
                if ($valueItem !== '0') $values[] = $valueItem;
            }
            $confValue = implode(Axis_Config::MULTI_SEPARATOR, $values);
        }

        $value = Axis::single('core/config_value')->select()
            ->where('path = ?', $path)
            ->where('site_id = ?', $siteId)
            ->fetchRow();
        /*
         * if such row not founded then create new record
         * It possible when we redeclare global config-value for site
         */
        if (!$value) {
            $value = Axis::single('core/config_value')->createRow(array(
                'config_field_id' => $field->id,
                'path'            => $path,
                'site_id'         => $siteId
            ));
        }
        $value->value = $confValue;
        $value->save();
        
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Configuration option was saved successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    public function useGlobalAction()
    {
        $this->_helper->layout->disableLayout();
        $siteId = (int) $this->_getParam('siteId');
        if (!$siteId)
            return;

        $pathItems = Zend_Json_Decoder::decode($this->_getParam('pathItems'));
        $where = array($this->db->quoteInto('site_id = ?', $siteId));
        foreach ($pathItems as $path) {
            $where[1] = $this->db->quoteInto('path = ?', $path);
            Axis::single('core/config_value')->delete($where);
        }
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Use global successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    public function copyGlobalAction()
    {
        $this->_helper->layout->disableLayout();
        $siteId = (int) $this->_getParam('siteId');
        if (!$siteId)
            return;

        $pathItems = Zend_Json_Decoder::decode($this->_getParam('pathItems'));
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
        $this->_helper->json->sendSuccess();
    }

    public function getFieldTypesAction()
    {
        $this->_helper->layout->disableLayout();
        $data = array();
        foreach (Axis_Collect_Configuration_Field::collect() as $id => $type) {
            $data[] = array('id' => $type, 'type' => $type);
        }
        array_unshift($data, array('id' => '', 'type' => null));
        $this->_helper->json->sendSuccess(array(
            'data' => $data
        ));
    }

    public function getFieldModelsAction()
    {
        $this->_helper->layout->disableLayout();
        $data = array();
        foreach (Axis_Collect_Collect::collect() as $id => $type) {
            $data[] = array('id' => strtolower($id), 'name' => $type);
        }
        sort($data);
        array_unshift($data, array('id' => '', 'name' => null));
        $this->_helper->json->sendSuccess(array(
            'data' => $data
        ));
    }

    public function saveFieldAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->json->sendJson(array(
            'success' => Axis::single('core/config_field')
                            ->save($this->_getAllParams()),
        ));
    }
}