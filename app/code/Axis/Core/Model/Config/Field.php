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
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Config_Field extends Axis_Db_Table
{
    protected $_name = 'core_config_field';

    protected $_primary = 'id';

    protected $_rowClass = 'Axis_Core_Model_Config_Field_Row';

    protected $_selectClass = 'Axis_Core_Model_Config_Field_Select';

    /**
     * Insert or update config field
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->select()
            ->where('path = ?', $data['path'])
            ->fetchRow();

        if (!$row) {
            $row = $this->createRow();
        } 
        $row->setFromArray($data);
        
        //before save
        if (empty($row->config_options)) {
            $row->config_options = new Zend_Db_Expr('NULL');
        }
        $row->lvl = count(explode('/', $row->path));
        
        if ($row->lvl <= 2) {
            $row->config_type = '';
        }
        
        $row->save();
        
        return $row;
    }

    /**
     * Removes config field, and all of it childrens
     * Provide fluent interface
     * @param string $path
     * @return bool
     */
    public function remove($path)
    {
        $this->delete("path LIKE '{$path}%'");
        Axis::single('core/config_value')->remove($path);
        return $this;
    }

    /**
     * Add config field row, and config value,
     * if $data['path'] has third level of config
     *
     * @param string path 'root'|'root/branch/config_field'
     * @param string title 'root'|'root/branch/config_field'
     * @param string $value Config field value
     * @param string $type 'bool|multiple|string|select|text|handler'
     * @param string $description Config field description
     * @param array $data
     *  model => '',
     *  model_assigned_with => '',
     *  config_options = 'red,blue,green',
     * @return Axis_Core_Model_Config_Field Provides fluent interface
     */
    public function add(
            $path,
            $title,
            $value = '',
            $type = 'string',
            $description = '',
            $data = array())
    {
        $configEntries = explode('/', $path);
        $checkBeforeInsert = true;
        $title = explode('/', $title);

        if (is_array($description)) {
            $data = $description;
            $description = '';
        }

        $rowData = array('lvl' => 0);
        foreach ($configEntries as $configEntry) {
            if (++$rowData['lvl'] == 1) {
                $rowData['path'] = $configEntry;
            } else {
                $rowData['path'] .= '/' . $configEntry;
            }

            $rowData['title'] = isset($title[$rowData['lvl']-1]) ?
                $title[$rowData['lvl']-1] : $title[0];

            $rowData = array_merge(array(
                'config_type' => 'string',
                'description' => '',
                'model'       => isset($data['model']) ? $data['model'] : '',
                'model_assigned_with' => isset($data['model_assigned_with']) ?
                    $data['model_assigned_with'] : '',
                'translation_module' => isset($data['translation_module']) ?
                    $data['translation_module'] : new Zend_Db_Expr('NULL')
            ), $rowData);

            if ($rowData['lvl'] == 3) {
                $rowData['config_type'] = $type;
                $rowData['description'] = $description;
                $rowData = array_merge($data, $rowData);
            }

            if ($checkBeforeInsert) {
                $rowField = $this->select()
                    ->where('path = ?', $rowData['path'])
                    ->fetchRow();
                if ($rowField) {
                    continue;
                } else {
                    $checkBeforeInsert = false;
                }
            }
            $rowField = $this->createRow($rowData);
            $rowField->save();
        }

        if ($rowData['lvl'] == 3) {
            $modelValue = Axis::single('core/config_value');
            $rowValue = $modelValue->select()
                ->where('path = ?', $rowData['path'])
                ->fetchRow();
            if (!$rowValue) {
                $rowValue = $modelValue->createRow();
            }
            if ($rowData['config_type'] == 'handler') {
                $class = 'Axis_Config_Handler_' . ucfirst($rowData['model']);
                $value = call_user_func(
                    array($class, 'getSaveValue'), $value
                );
            }
            $rowValue->setFromArray(array(
                'config_field_id' => $rowField->id,
                'path'            => $rowData['path'],
                'site_id'         => 0,
                'value'           => $value
            ));
            $rowValue->save();
        }

        return $this;
    }
}