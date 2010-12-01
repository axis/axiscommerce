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
        
        $this->_helper->json->sendJson(
            Axis::single('core/config_field')->getNodes($node), false, false
        );
    }
    
    public function listAction()
    {
        $this->_helper->layout->disableLayout();
        $path = $this->_getParam('path');
        $path = $this->db->quote(($path == '0') ? '%' : $path . '/%');
        $siteId = $this->_getParam('siteId');
        
        $values = array();
        $rows = Axis::single('core/config_field')->fetchAll(
            array('lvl = 3', "path LIKE $path"), "title ASC"
        );
        foreach ($rows as $row) {
            $values[$row->path] = array(
                'title' => Axis::translate($row->getTranslationModule())
                    ->__($row->title),
                'value' => '',
                'id'    => $row->id,
                'model' => $row->model,
                'path'  => $row->path,
                'config_type'  => $row->config_type,
                'from'  => ''
            );
            if (null != $row->config_options) {
                $values[$row->path]['config_options'] =
                    $this->_optionsToArray($row->config_options);
            }
        }
        
        $rows = Axis::single('core/config_value')->fetchAll(
            array(
                "path LIKE $path", 
                $this->db->quoteInto('site_id IN(0, ?)', $siteId)
            ),
            'site_id'
        );
        
        foreach ($rows as $row) {
            $model = $values[$row->path]['model'];
            $values[$row->path]['value'] =
                !empty($model) && ($values[$row->path]['config_type'] != 'handler') ?
                $this->_getName($model, $row->value) : $row->value ;
                
            if ($values[$row->path]['config_type'] == 'handler' 
                && strtolower($values[$row->path]['model']) == 'crypt') {

                $values[$row->path]['value'] = '****************';
            }

            if ($values[$row->path]['config_type'] == 'bool') {
                $values[$row->path]['value'] = $row->value ? 'Yes' : 'No' ;
            }
            
            if (in_array($values[$row->path]['config_type'], array('select', 'multiple'))
                && isset($values[$row->path]['config_options'][$values[$row->path]['value']])) {

                $values[$row->path]['value'] = 
                    $values[$row->path]['config_options'][$values[$row->path]['value']];
            }

            $values[$row->path]['from'] = $row->site_id ? 'site' : 'global';
        }
        $this->_helper->json->sendSuccess(array(
            'data' => array_values($values)
        ));
    }
    
    public function editAction()
    {
        $this->_helper->layout->disableLayout();
        $siteId = $this->_getParam('siteId');
        $path = $this->_getParam('path');
        
        $field = Axis::single('core/config_field')->fetchRow(
            $this->db->quoteInto("path = ?", $path)
        );
        
        $this->view->confField = $field->toArray();
        $this->view->confField['description'] = 
            Axis::translate($field->getTranslationModule())->__(
                $this->view->confField['description']
            );
        $this->view->confField['title'] = 
            Axis::translate($field->getTranslationModule())->__(
                $this->view->confField['title']
            );
        
        $this->view->confValue = Axis::single('core/config_value')
            ->getValue($path, $siteId);
            
        $this->view->siteId = $siteId;
        $this->view->configPath = $path;
        
        if (!empty($field->model)) {
            if ($field->config_type != 'handler') {
                $this->view->confField['config_options'] =
                    $this->_collect($field->model, $field->model_assigned_with);
            } else {
                $this->view->confField['config_options'] = $this->_getHtml(
                    $field->model, 
                    Axis::single('core/config_value')->getValue($path, $siteId)
                );
            }
            
            $this->view->confValue = $this->_optionsToArray($this->view->confValue);
        } elseif ($field->config_type == 'select' 
            || $field->config_type == 'multiple') {

            $this->view->confField['config_options'] =
                $this->_optionsToArray($field->config_options);
            $this->view->confValue = $this->_optionsToArray($this->view->confValue);
        }
        
        $this->render();
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $path    = $this->_getParam('path');
        $siteId  = $this->_getParam('siteId');
        $confValue = $this->_getParam('confValue');
        
        $field = Axis::single('core/config_field')->fetchRow(
            $this->db->quoteInto('path = ?', $path)
        );
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
        
        $row = Axis::single('core/config_value')->fetchRow(array(
            $this->db->quoteInto('path = ?', $path),
            $this->db->quoteInto('site_id = ?', $siteId)
        ));
        /*
         * if such row not founded then create new record
         * It possible when we redeclare global config-value for site
         */
        if (!$row) {
            $row = Axis::single('core/config_value')->createRow();
            $row->config_field_id = $field->id;
            $row->path = $path;
            $row->site_id = $siteId;
        }
        
        $row->value = $confValue;
        $row->save();
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
        foreach ($pathItems as $path) {
            $where[1] = $this->db->quoteInto('path = ?', $path);
            
            /* delete exists value */
            Axis::single('core/config_value')->delete($where);
            
            /* get globalRow */
            $globalRow = Axis::single('core/config_value')->fetchRow(array(
                $where[1],
                'site_id = 0'
            ));
            
            if ($globalRow) {
                Axis::single('core/config_value')->insert(array(
                    'path'    => $path,
                    'site_id' => $siteId,
                    'value'   => $globalRow->value
                ));
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