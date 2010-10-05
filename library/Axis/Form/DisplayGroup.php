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
 * @package     Axis_Form
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Form
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Form_DisplayGroup extends Zend_Form_DisplayGroup
{
    const DECORATOR = 'DECORATOR';
    const ELEMENT = 'ELEMENT';
    
    /**
     * Array of Axis_Form_Row
     * @var array
     */
    private $_rows = array();
    
    /**
     * Array of Axis_Form_Column
     * @var array
     */
    private $_cols = array();
    
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Rowset')
                ->addDecorator('Colset')
                ->addDecorator('FormElements')
                ->addDecorator('Fieldset');
        }
    }
    
    public function addColumn($elements, $name, $options = null)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }
        
        $group = array();
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    unset($this->_elements[$element]);
                    $group[] = $add;
                }
            }
        }
        if (empty($group)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No valid elements specified for column');
        }

        $name = (string) $name;

        if (is_array($options)) {
            $options['elements'] = $group;
        } elseif ($options instanceof Zend_Config) {
            $options = $options->toArray();
            $options['elements'] = $group;
        } else {
            $options = array('elements' => $group);
        }

        if (isset($options['columnClass'])) {
            $class = $options['columnClass'];
            unset($options['columnClass']);
        } else {
            $class = 'Axis_Form_Column';//$this->getDefaultRowsetClass();
        }
        
        if (!class_exists($class)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
        }
        $this->_cols[$name] = new $class(
            $name, 
            $this->getPluginLoader(self::DECORATOR),
            $options
        );

        if (!empty($this->_rowsPrefixPaths)) {
            $this->_cols[$name]->addPrefixPaths($this->_rowsPrefixPaths);
        }

        return $this;
    }
    
    /**
     * Retrieve a column
     * 
     * @param  string $name 
     * @return Axis_Form_Column
     */
    public function getColumn($name)
    {
        $name = (string) $name;
        if (isset($this->_cols[$name])) {
            return $this->_cols[$name];
        }
        return null;
    }
    
    /**
     * Returns array of columns
     * @return array
     */
    public function getColumns()
    {
        return $this->_cols;
    }
    
    /**
     * Checks is there are some columns in row
     * @return bool
     */
    public function hasColumns()
    {
        return (bool) count($this->_cols);
    }
    
    public function addRow($elements, $name, $options = null)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }
        
        $group = array();
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    unset($this->_elements[$element]);
                    $group[] = $add;
                }
            }
        }
        if (empty($group)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No valid elements specified for row');
        }

        $name = (string) $name;

        if (is_array($options)) {
            $options['elements'] = $group;
        } elseif ($options instanceof Zend_Config) {
            $options = $options->toArray();
            $options['elements'] = $group;
        } else {
            $options = array('elements' => $group);
        }

        if (isset($options['rowClass'])) {
            $class = $options['rowClass'];
            unset($options['rowClass']);
        } else {
            $class = 'Axis_Form_Row';//$this->getDefaultRowsetClass();
        }
        
        if (!class_exists($class)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
        }
        $this->_rows[$name] = new $class(
            $name, 
            $this->getPluginLoader(self::DECORATOR),
            $options
        );

        if (!empty($this->_rowsPrefixPaths)) {
            $this->_rows[$name]->addPrefixPaths($this->_rowsPrefixPaths);
        }

        return $this;
    }
    
    /**
     * Retrieve a row
     * 
     * @param  string $name 
     * @return Axis_Form_Row
     */
    public function getRow($name)
    {
        $name = (string) $name;
        if (isset($this->_rows[$name])) {
            return $this->_rows[$name];
        }

        return null;
    }
    
    public function getRows()
    {
        return $this->_rows;
    }
    
    public function hasRows()
    {
        return (bool) count($this->_rows);
    }
}