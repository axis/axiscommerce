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
class Axis_Core_Model_Config_Builder
{
    /**
     *
     * @var array
     */
    protected $_rowField = array();

    /**
     *
     * @var array
     */
    protected $_defaultsRowField = array();

    /**
     *
     * @var mixed
     */
    protected $_rawValue = null;

    /**
     *
     * @var array
     */
    protected $_path = array();

    /**
     *
     * @var bool
     */
    protected $_isContainer = false;

    public function __construct()
    {
        $this->flushDefaults();
    }

    /**
     *
     * @return \Axis_Core_Model_Config_Builder
     */
    public function flushDefaults()
    {
        $this->_defaultsRowField = array(
            'type'               => 'text',
            'description'        => '',
            'model'              => '',
            'translation_module' => new Zend_Db_Expr('NULL')
        );
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setDefaultType($value)
    {
        $this->_defaultsRowField['type'] = $value;
        return $this;
    }

    /**
     *
     * @param mixed $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setDefaultDescription($value)
    {
        $this->_defaultsRowField['description'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setDefaultModel($value)
    {
        $this->_defaultsRowField['model'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setDefaultTranslation($value)
    {
        $this->_defaultsRowField['translation_module'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setTitle($value)
    {
        $this->_rowField['title'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setType($value)
    {
        $this->_rowField['type'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setModel($value)
    {
        $this->_rowField['model'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setDescription($value)
    {
        $this->_rowField['description'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setTranslation($value)
    {
        $this->_rowField['translation_module'] = $value;
        return $this;
    }

    /**
     *
     * @param string $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function setValue($value)
    {
        $this->_rawValue = $value;
        return $this;
    }

    /**
     *
     * @param string $path
     * @param string $title
     * @return \Axis_Core_Model_Config_Builder
     */
    public function section($path = null, $title = null)
    {
        $this->_savePrevious();
        if ('/' === $path) {
            $this->_path = array();
        } elseif (in_array(ltrim($path, '/'), $this->_path)) {
            while (ltrim($path, '/') !== array_pop($this->_path)) {}
        } else {
            $this->_isContainer = true;
            array_push($this->_path, $path);
            $this->_rowField = array(
                'path' => implode('/', $this->_path),
                'lvl'  => count($this->_path)
            );
            if (null !== $title) {
                $this->setTitle($title);
            }
        }
        return $this;
    }

    /**
     *
     * @param string $path
     * @param string $title
     * @param mixed $value
     * @return \Axis_Core_Model_Config_Builder
     */
    public function option($path, $title = null, $value = null)
    {
        $this->_savePrevious();
        $this->_rawValue = null;
        $this->_isContainer = false;
        array_push($this->_path, $path);
        $this->_rowField = array(
            'path' => implode('/', $this->_path),
            'lvl'  => count($this->_path)
        );
        array_pop($this->_path);
        if (null !== $title) {
            $this->setTitle($title);
        }
        if (null !== $value) {
            $this->setValue($value);
        }
        return $this;
    }

    protected function _savePrevious()
    {
        $rowData = $this->_rowField;
        $this->_rowField = array();
        if (empty($rowData)) {
          return;
        }
        $modelField = Axis::single('core/config_field');
        $rowField = $modelField->select()
            ->where('path = ?', $rowData['path'])
            ->fetchRow();
        if (!$rowField) {
            $rowData = array_merge($this->_defaultsRowField, $rowData);
            $rowField = $modelField->createRow();
        }
        if ($this->_isContainer) {

            $rowData = array_merge($rowData, array(
                'type'  => '',
                'model' => '',
            ));
        }
        $rowField->setFromArray($rowData);
        $rowField->save();

        if ($this->_isContainer) {
            return;
        }
        $modelValue = Axis::single('core/config_value');
        $rowValue = $modelValue->select()
            ->where('path = ?', $rowData['path'])
            ->where('config_field_id = ?', $rowField->id)
            ->where('site_id = ?', 0)
            ->fetchRow();
        if (!$rowValue) {
            $rowValue = $modelValue->createRow(array(
                'config_field_id' => $rowField->id,
                'path'            => $rowData['path'],
                'site_id'         => 0
            ));
        }
        if (null !== $this->_rawValue) {
            $value = $this->_rawValue;

            if (!empty($rowData['model'])) {
                $class = Axis::getClass($rowData['model']);
                if (class_exists($class)
                    && in_array('Axis_Config_Option_Encodable_Interface', class_implements($class))) {

                    $value = Axis::model($rowData['model'])->encode($value);
                }
            }
            $rowValue->value = $value;
        }
        $rowValue->save();
    }

    /**
     * Removes config field, and all of it childrens
     * Provide fluent interface
     * @param string $path
     * @return \Axis_Core_Model_Config_Builder
     */
    public function remove($path)
    {
        Axis::single('core/config_value')->delete("path LIKE '{$path}%'");
        Axis::single('core/config_field')->delete("path LIKE '{$path}%'");
        return $this;
    }
}