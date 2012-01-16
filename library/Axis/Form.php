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
 * @copyright   Copyright 2008-2012 Axis
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

    protected $_translatorModule = 'Axis_Core';

    protected $_eventPrefix = null;

    /**
     * Constructor. Overriden to add events after form initialization
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->setDefaultDisplayGroupClass('Axis_Form_DisplayGroup');
        $this->addPrefixPath('Axis_Form_Decorator', 'Axis/Form/Decorator', 'decorator');
        $this->setTranslator(Axis::translate($this->_translatorModule)->getAdapter());

        parent::__construct($options);

        Axis::dispatch('form_construct_after', $this); // global event for all forms
        if ($this->_eventPrefix) {
            Axis::dispatch($this->_eventPrefix . '_construct_after', $this);
        }
    }

    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormElements',
            'ActionBar',
            array('HtmlTag', array('tag' => 'ul')),
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
        $element = parent::createElement($type, $name, $options);

        $liOptions = array(
            'class' => 'element-row'
        );

        switch ($type) {
            case 'submit': case 'button':
                $element->clearDecorators()
                    ->addDecorator('ViewHelper');
            break;
            case 'hidden':
                $liOptions = array(
                    'class' => 'element-hidden',
                    'style' => 'display: none;'
                );
            default:
                $getId = create_function(
                    '$decorator',
                    'return $decorator->getElement()->getId() . "-row";'
                );
                $element->clearDecorators()
                    ->addDecorator('ViewHelper')
                    ->addDecorator('Errors')
                    ->addDecorator('Description', array('tag' => 'small', 'class' => 'description'))
                    ->addDecorator('Label', array(
                        'tag' => '',
                        'placement' => 'prepend'
                    ))
                    ->addDecorator('HtmlTag', array_merge($liOptions, array(
                        'tag' => 'li',
                        'id'  => array('callback' => $getId)
                    )));
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
        $elementsTranslator = Axis::translate('Axis_Core')->getAdapter();
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
        $elementsTranslator = Axis::translate('Axis_Core')->getAdapter();
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
