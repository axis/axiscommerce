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
 * @copyright   Copyright 2008-2011 Axis
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
            $data[$row->id] = $row->toArray();
            $data[$row->id]['title'] =
                Axis::translate($row->getTranslationModule())->__($row->title);

//            if (empty($row->model)) {
//                $_value = $row->value;
//            } elseif ('handler' == $row->config_type && 'Crypt' == $row->model){
//                $_value = '****************';
//            } else {
//                
//            }
            
//            if ('bool' == $row->config_type) {
//                $_value = Axis_Core_Model_Config_Value_Boolean::getConfigOptionValue($row->value);
//            } elseif ('handler' == $row->config_type && 'Crypt' == $row->model) {
//                $_value = '****************';
//            } else
            if (/*'handler' !== $row->config_type && */!empty($row->model) 
                && method_exists($row->model, 'getConfigOptionValue')) {
                
                $_value = call_user_func(
                    array($row->model, 'getConfigOptionValue'), $row->value
                );
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
        
        $this->view->siteId = $siteId;
        $this->view->configPath = $path;

        $row = Axis::single('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();

        $translator = Axis::translate($row->getTranslationModule());
        $row->description = $translator->__($row->description);
        $row->title = $translator->__($row->title);
       
        $value = Axis::config($path);
        if ($value instanceof Axis_Config) {
            $value = $value->toArray();
        }

        if (!empty($row->model)) {
            
//            if ('handler' === $row->config_type) {
//                $this->view->handlerHtml = call_user_func(
//                    array('Axis_Config_Handler_' . $row->model, 'getHtml'), 
//                    $value, 
//                    $this->view
//                );
//            } else {
                if (method_exists($row->model, 'getConfigOptionsArray')) {
                    $this->view->options = call_user_func(
                        array($row->model, 'getConfigOptionsArray')
                    );
                }
                //@todo $row->getBackend()
                $modelBackend = null;
                if (class_exists($row->model)) {
                    $modelBackend = new $row->model();
                }

                if ($modelBackend instanceof Axis_Config_Option_Encodable_Interface) {
                    $value = $modelBackend->decodeConfigOptionValue($value);
                }
//            } 
        }
        $this->view->value = $value;
        
        $this->view->row = $row;       
        $this->render();
    }

    public function saveAction()
    {
        $path   = $this->_getParam('path');
        $siteId = $this->_getParam('siteId');
        $value  = $this->_getParam('confValue');

        $rowField = Axis::single('core/config_field')->select()
            ->where('path = ?', $path)
            ->fetchRow();

        $modelBackend = null;
        if (class_exists($rowField->model)) {
            $modelBackend = new $rowField->model();
        }
        
        if ($modelBackend instanceof Axis_Config_Option_Encodable_Interface) {
            $value = $modelBackend->encodeConfigOptionValue($value);
        } elseif ($modelBackend instanceof Axis_Config_Option_Array_Interface
            && strchr(Axis_Config::MULTI_SEPARATOR, (string)$value)) {
            //@todo use implode/explode default encode/decode on axis_collection
            $value = implode(Axis_Config::MULTI_SEPARATOR, (string)$value);
        }

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