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
class Axis_Form extends Zend_Form
{
    /**
     * @var Axis_Form_ActionBar
     */
    private $_actionBar = null;
    
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
    
    protected $_translatorModule = 'core';
    
    public function init()
    {
        $this->setDefaultDisplayGroupClass('Axis_Form_DisplayGroup');
        $this->addPrefixPath('Axis_Form_Decorator', 'Axis/Form/Decorator', 'decorator');
        $this->setTranslator(Axis::translate($this->_translatorModule)->getTranslator());
    }
    
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'Rowset',
            'Colset',
            'FormElements',
            'ActionBar',
            array('HtmlTag', array('tag' => 'div')),
            array('Form', array('class' => 'axis-form'))
        ));
    }
    
    /**
     * Add action bar with set of buttons to form
     * 
     * @param array $elements
     * @return Axis_Form
     */
    public function addActionBar(array $elements)
    {
        if (null !== $this->_actionBar) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Cannot assign more then one action bars to one form');
        }
        
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    unset($this->_order[$element]);
                    $group[] = $add;
                }
            }
        }
        if (empty($group)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No valid elements specified for actionbar');
        }
        
        $options = array('elements' => $group);
        
        $this->_actionBar = new Axis_Form_ActionBar(
            'actionbar', 
            $this->getPluginLoader(self::DECORATOR),
            $options
        );
        
        return $this;
    }
    
    /**
     * @return Axis_Form_ActionBar
     */
    public function getActionBar()
    {
        return $this->_actionBar;
    }
    
    public function addColumn(array $elements, $name, $options = null)
    {
        $group = array();
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    unset($this->_order[$element]);
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
                    unset($this->_order[$element]);
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
    
    /**
     * Create an element
     *
     * Acts as a factory for creating elements. Elements created with this 
     * method will not be attached to the form, but will contain element 
     * settings as specified in the form object (including plugin loader 
     * prefix paths, default decorators, etc.).
     * 
     * @param  string $type 
     * @param  string $name 
     * @param  array|Zend_Config $options 
     * @return Zend_Form_Element
     */
    public function createElement($type, $name, $options = null)
    {
        if (!is_string($type)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Element type must be a string indicating type');
        }

        if (!is_string($name)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Element name must be a string');
        }

        $prefixPaths              = array();
        $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
        if (!empty($this->_elementPrefixPaths)) {
            $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if ((null === $options) || !is_array($options)) {
            $options = array('prefixPath' => $prefixPaths);
        } elseif (is_array($options)) {
            if (array_key_exists('prefixPath', $options)) {
                $options['prefixPath'] = array_merge($prefixPaths, $options['prefixPath']);
            } else {
                $options['prefixPath'] = $prefixPaths;
            }
        }

        $class = $this->getPluginLoader(self::ELEMENT)->load($type);
        $element = new $class($name, $options);
        
        switch ($type) {
            case 'submit': case 'button':
                $element->clearDecorators()
                    ->addDecorator('ViewHelper');
            break;
            default:
                $element->clearDecorators()
                    ->addDecorator('ViewHelper')
                    ->addDecorator('Errors')
                    ->addDecorator('Description', array('tag' => 'small', 'class' => 'description'))
                    ->addDecorator('Label', array(
                        'tag' => '',
                        'placement' => 'prepend'
                    ));
            break;
        }

        return $element;
    }
    
    /**
     * Validate the form
     * Overloaded to load validation translations from core module
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        if (!is_array($data)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(__CLASS__ . '::' . __METHOD__ . ' expects an array');
        }
        $translator = $this->getTranslator();
        $elementsTranslator = Axis::translate('core')->getTranslator();
        $valid      = true;
        
        if ($this->isArray()) {
            $data = $this->_dissolveArrayValue($data, $this->getElementsBelongTo());
        }
        
        foreach ($this->getElements() as $key => $element) {
            $element->setTranslator($elementsTranslator);
            if (!isset($data[$key])) {
                $valid = $element->isValid(null, $data) && $valid;
            } else {
                $valid = $element->isValid($data[$key], $data) && $valid;
            }
        }
        foreach ($this->getSubForms() as $key => $form) {
            $form->setTranslator($translator);
            if (isset($data[$key])) {
                $valid = $form->isValid($data[$key]) && $valid;
            } else {
                $valid = $form->isValid($data) && $valid;
            }
        }
        
        $this->_errorsExist = !$valid;
        
        // If manually flagged as an error, return invalid status
        if ($this->_errorsForced) {
            return false;
        }
        
        return $valid;
    }

    /**
     * Validate a partial form
     * Overloaded to load validation translations from core module
     *
     * Does not check for required flags.
     *
     * @param  array $data
     * @return boolean
     */
    public function isValidPartial(array $data)
    {
        if ($this->isArray()) {
            $data = $this->_dissolveArrayValue($data, $this->getElementsBelongTo());
        }

        $translator        = $this->getTranslator();
        $elementsTranslator = Axis::translate('core')->getTranslator();
        $valid             = true;
        $validatedSubForms = array();
        
        foreach ($data as $key => $value) {
            if (null !== ($element = $this->getElement($key))) {
                if (null !== $elementsTranslator) {
                    $element->setTranslator($elementsTranslator);
                }
                $valid = $element->isValid($value, $data) && $valid;
            } elseif (null !== ($subForm = $this->getSubForm($key))) {
                if (null !== $translator) {
                    $subForm->setTranslator($translator);
                }
                $valid = $subForm->isValidPartial($data[$key]) && $valid;
                $validatedSubForms[] = $key;
            }
        }
        foreach ($this->getSubForms() as $key => $subForm) {
            if (!in_array($key, $validatedSubForms)) {
                if (null !== $translator) {
                    $subForm->setTranslator($translator);
                }

                $valid = $subForm->isValidPartial($data) && $valid;
            }
        }
        
        $this->_errorsExist = !$valid;
        return $valid;
    }
}