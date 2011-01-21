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
 * @subpackage  Axis_Core_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Core_Box_Abstract
{
    /**
     * @var string
     */
    protected $_title = '';

    /**
     * @var string
     */
    protected $_class = '';
    /**
     * @var bool
     */
    protected $_disableWrapper = false;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @static
     * @var Zend_View
     */
    public static $view;

    /**
     *
     * @var bool
     */
    protected $_isAllowed = true;

    /**
     * Temporary container for array of called boxes.
     * Used for possibility to render box in box, without
     * loss 'box' variable at the view point
     *
     * @var array
     */
    private $_stack = array();

    /**
     * @static
     * @param Zend_View $view
     */
    public static function setView($view)
    {
        self::$view = $view;
    }

    /**
     *
     * @return Zend_View
     */
    public function getView()
    {
        return self::$view;
    }

    public function __construct($config = array())
    {
        if (null === self::$view) {
            self::$view = Zend_Layout::getMvcInstance()->getView();
        }
        if (!$this->_isAllowed = in_array(
                $config['boxCategory'] . '_' . $config['boxModule'],
                array_keys(Axis::app()->getModules()))) {

            return;
        }
        $this->updateData($config, true);
        $this->init();
    }

    public function toHtml()
    {
        if (!$this->_isAllowed
            || false === $this->initData()
            || !$this->hasContent() ) {

            return '';
        }

        if (empty($this->_data['template'])) {
            $templateName = $this->getData('boxName');
            $templateName[0] = strtolower($templateName[0]);
            $this->setData('template', strtolower($this->getData('boxModule'))
                . '/box/' . $templateName . '.phtml');
        }

        self::$view->box = $this;
        if (!Zend_Registry::isRegistered('axis_box/stack')) {
            Zend_Registry::set('axis_box/stack', array($this));
        } else {
            $this->_stack = Zend_Registry::get('axis_box/stack');
            $this->_stack[] = $this;
            Zend_Registry::set('axis_box/stack', $this->_stack);
        }

        if (!empty($this->_data['tabContainer'])) {
            $result = self::$view->render('core/box/tab.phtml');
        } elseif ($this->getData('disableWrapper')) {
            $result = self::$view->render($this->getData('template'));
        } else {
            $result = self::$view->render('core/box/box.phtml');
        }

        $this->_stack = Zend_Registry::get('axis_box/stack');
        array_pop($this->_stack);
        Zend_Registry::set('axis_box/stack', $this->_stack);
        if ($count = count($this->_stack)) {
            self::$view->box = $this->_stack[$count - 1];
        } else {
            unset(self::$view->box);
        }

        return $result;
    }

    public function getData($key = null, $default = null)
    {
        if (null === $key) {
            return $this->_data;
        }
        if (strstr($key, '/')) {
            $result = $this->_data;
            foreach (explode('/', $key) as $key) {
                if (!is_array($result) || !isset($result[$key])) {
                    return $default;
                }
                $result = $result[$key];
            }
            return $result;
        }
        return isset($this->_data[$key]) ? $this->_data[$key] : $default;
    }

    /**
     * Add key => value pair to data array
     *
     * @param string $key
     * @param mixed $value
     * @return Axis_Core_Box_Abstract Provides fluent interface
     */
    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function updateData(array $data, $reset = false)
    {
        if ($reset) {
            $this->_data = array_merge($this->_data, array(
                'title'          => $this->_title,
                'class'          => $this->_class,
                'url'            => $this->_url,
                'disableWrapper' => $this->_disableWrapper,
                'tabContainer'   => $this->_tabContainer,
                'template'       => $this->_template
            ));
        }

        if (!empty($data['config'])) {
            $additional = $data['config'];
            unset($data['config']);
            foreach(explode(',', $additional) as $opt) {
                list($key, $value) = explode(':', $opt);
                $data[$key] = $value;
            }
        }
        foreach ($data as $key => $value) {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    public function hasData($key)
    {
        if (strstr($key, '/')) {
            $brunch = $this->_data;
            foreach (explode('/', $key) as $key) {
                if (!is_array($brunch) || !isset($brunch[$key])) {
                    return false;
                }
                $brunch = $brunch[$key];
            }
            return true;
        } else {
            return isset($this->_data[$key]);
        }
    }

    public function __get($key)
    {
        return $this->getData($key);
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function __call($name, $arguments)
    {
        $key = substr($name, 3);
        $key[0] = strtolower($key[0]);
        switch (substr($name, 0, 3)) {
            case 'has':
                return $this->hasData($key);
            case 'get':
                return $this->getData($key);
            case 'set':
                $this->setData($key, $arguments[0]);
                return $this;
        }
        throw new Axis_Exception(Axis::translate('core')->__(
            "Call to undefined method '%s'", get_class($this) . '::' . $name
        ));
    }

    public function init() {}

    /**
     * @return mixed null|mixed
     */
    public function initData()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasContent()
    {
        return true;
    }
}