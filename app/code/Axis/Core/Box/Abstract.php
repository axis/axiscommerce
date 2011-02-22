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
abstract class Axis_Core_Box_Abstract extends Axis_Object
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
     * @var string
     */
    protected $_url = '';

    /**
     * @var bool
     */
    protected $_disableWrapper = false;

    /**
     * @var string
     */
    protected $_tabContainer = null;

    /**
     * @var string
     */
    protected $_template = null;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @static
     * @var Zend_View
     */
    protected static $_view;

    /**
     *
     * @var bool
     */
    protected $_enabled = true;

    /**
     * Temporary container for array of called boxes.
     * Used for possibility to render box in box, without
     * loss 'box' variable at the view point
     *
     * @var array
     */
    private static $_stack = array();

    /**
     * @static
     * @param Zend_View $view
     */
    public static function setView($view)
    {
        self::$_view = $view;
    }

    /**
     *
     * @return Zend_View
     */
    public function getView()
    {
        return self::$_view;
    }

    public function __construct($config = array())
    {
        if (null === self::$_view) {
            $this->setView(
                Axis_Layout::getMvcInstance()->getView()
            );
        }
        // why not get_class($this)
        $this->_enabled = in_array(
            $config['box_namespace'] . '_' . $config['box_module'],
            array_keys(Axis::app()->getModules())
        );
        if (!$this->_enabled) {
            return;
        }
        $this->refresh()
            ->updateData($config)
            ->init();
    }

    public function toHtml()
    {
        if (!$this->_enabled
            || false === $this->initData()
            || !$this->hasContent()) {

            return '';
        }
        $template = $this->getData('template');
        if (empty($template)) {
            $template = $this->box_name . '.phtml';
            $template = strtolower(substr($template, 0, 1)) . substr($template, 1);
            $template = strtolower($this->box_module) . '/box/' . $template;
            $this->template = $template;
        }

        $this->getView()->box = self::$_stack[] = $this;

        if (!empty($this->_data['tab_container'])) {
            $path = 'core/box/tab.phtml';
        } elseif ($this->disable_wrapper) {
            $path = $this->getData('template');
        } else {
            $path = 'core/box/box.phtml';
        }

        $html = null;
        $obStartLevel = ob_get_level();
        try {
            $html = $this->getView()->render($path);
        } catch (Exception $e) {
            while (ob_get_level() > $obStartLevel) {
                $html .= ob_get_clean();
            }
            throw $e;
        }

        unset($this->getView()->box);
        array_pop(self::$_stack);
        if (count(self::$_stack)) {
            $this->getView()->box = end(self::$_stack);
        }
        return $html;
    }

    public function hasData($key)
    {
        if (strstr($key, '/')) {
            $_data = $this->_data;
            foreach (explode('/', $key) as $key) {
                if (!is_array($_data) || !isset($_data[$key])) {
                    return false;
                }
                $_data = $_data[$key];
            }
            return true;
        } 
        return isset($this->_data[$key]);
    }
    
    public function getData($key = null, $default = null)
    {
        if (null === $key) {
            return $this->_data;
        }
        if (strstr($key, '/')) {
            $_data = $this->_data;
            foreach (explode('/', $key) as $key) {
                if (!is_array($_data) || !isset($_data[$key])) {
                    return $default;
                }
                $_data = $_data[$key];
            }
            return $_data;
        }
        return isset($this->_data[$key]) ? $this->_data[$key] : $default;
    }

    public function refresh()
    {
        $this->_data = array_merge($this->_data, array(
            'title'           => $this->_title,
            'class'           => $this->_class,
            'url'             => $this->_url,
            'disable_wrapper' => $this->_disableWrapper,
            'tab_container'   => $this->_tabContainer,
            'template'        => $this->_template
        ));
        return $this;
    }

    public function updateData(array $data)
    {
        //@todo why not setFromArray?
        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }
        return $this;
    }
    
    public function init() {}

    /**
     * @return mixed void|mixed
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