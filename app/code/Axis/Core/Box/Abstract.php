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
 * @copyright   Copyright 2008-2012 Axis
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
    protected $_url = null;

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
    protected $_enabled = null;

    /**
     * Temporary container for array of called boxes.
     * Used for possibility to render box in box, without
     * loss 'box' variable at the view point
     *
     * @var array
     */
    private static $_stack = array();

    /**
     * Use this method to add the default cache tags, specific lifetime, etc.
     * All variables intialized in this method, can be overriden with box configuration.
     *
     * @return void
     */
    protected function _construct() {}

    /**
     * Use this method to initialize box variables.
     * If the method will return boolean false,
     * the box output will return an empty string.
     *
     * @return void|false
     */
    protected function _beforeRender() {}

    public function __construct($config = array())
    {
        list($namespace, $module, , $name) = explode('_', get_class($this));
        $this->_data = array(
            'box_namespace' => $namespace,
            'box_module'    => $module,
            'box_name'      => $name
        );

        $this->_construct();

        $this->refresh()
            ->setFromArray($config);
    }

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
        if (null === self::$_view) {
            $this->setView(Axis_Layout::getMvcInstance()->getView());
        }
        return self::$_view;
    }

    /**
     * @return boolean
     */
    protected function _isEnabled()
    {
        if (null === $this->_enabled) {
            $this->_enabled = in_array(
                $this->getData('box_namespace') . '_' . $this->getData('box_module'),
                array_keys(Axis::app()->getModules())
            );
        }
        return $this->_enabled;
    }

    /**
     * @return string
     */
    public function render()
    {
        $output = $this->_getCachedOutput();
        if (false !== $output) {
            return $output;
        }

        if (!$this->_isEnabled()) {
            $this->_setCachedOutput('');
            return '';
        }

        if (false === $this->_beforeRender()) {
            $html = '';
        } else {
            $this->getView()->box = self::$_stack[] = $this;

            if (!empty($this->_data['tab_container'])) {
                $path = 'core/box/tab.phtml';
            } elseif ($this->disable_wrapper) {
                $path = $this->getTemplate();
            } else {
                $path = 'core/box/box.phtml';
            }

            $html = '';
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
        }

        $this->_setCachedOutput($html);
        return $html;
    }

    /**
     * Retrieve the box template
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = $this->getData('template');
        if (empty($template)) {
            if (false === function_exists('lcfirst') ) {
                function lcfirst($str) {
                    return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
                }
            }

            $template = strtolower($this->getData('box_module'))
                . '/box/'
                . lcfirst($this->getData('box_name'))
                . '.phtml';

            $this->template = $template;
        }
        return $template;
    }

    /**
     * @return string
     */
    public function  __toString()
    {
        return $this->render();
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key)
    {
        if (strstr($key, '/')) {
            $_data = $this->_data;
            foreach (explode('/', $key) as $key) {
                if (!is_array($_data) || !array_key_exists($key, $_data)) {
                    return false;
                }
                $_data = $_data[$key];
            }
            return true;
        }
        return array_key_exists($key, $this->_data);
    }

    /**
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getData($key = null, $default = null)
    {
        if (null === $key) {
            return $this->_data;
        }
        if (strstr($key, '/')) {
            $_data = $this->_data;
            foreach (explode('/', $key) as $key) {
                if (!is_array($_data) || !array_key_exists($key, $_data)) {
                    return $default;
                }
                $_data = $_data[$key];
            }
            return $_data;
        }
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
    }

    /**
     * Fill the data array with default values of the box
     *
     * @return Axis_Core_Box_Abstract
     */
    public function refresh()
    {
        $this->_data = array_merge(
            $this->_data,
            array(
                'title'           => $this->_title,
                'class'           => $this->_class,
                'url'             => $this->_url,
                'disable_wrapper' => $this->_disableWrapper,
                'tab_container'   => $this->_tabContainer,
                'template'        => $this->_template
            )
        );
        return $this;
    }

    /**
     * Retrieve box output from the cache
     *
     * @return mixed
     */
    protected function _getCachedOutput()
    {
        if (0 === $this->_getCacheLifetime()) {
            return false;
        }
        return Axis::cache()->load($this->_getCacheKey());
    }

    /**
     * Saves output to the cache
     *
     * @return void
     */
    protected function _setCachedOutput($data)
    {
        if (0 === $this->_getCacheLifetime()) {
            return false;
        }
        Axis::cache()->save(
            $data,
            $this->_getCacheKey(),
            $this->_getCacheTags(),
            $this->_getCacheLifetime()
        );
    }

    /**
     * @return array
     */
    protected function _getCacheTags()
    {
        if ($this->hasData('cache_tags')) {
            $tags = $this->getData('cache_tags');
            if (!is_array($tags)) {
                $tags = (array)$tags;
            }
        } else {
            $tags = array();
        }

        $tags[] = 'boxes';
        return $tags;
    }

    /**
     * Returns cache lifetime in seconds
     * 0     - cache is disabled
     * null  - infinite cache lifetime
     * false - use cache tag lifetime or default lifetime
     *
     * @return int Time in seconds
     */
    protected function _getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return false;
        }
        return $this->getData('cache_lifetime');
    }

    /**
     * Returns additional data that affects box output.
     * This should be one-dimensional array.
     *
     * @return array
     */
    protected function _getCacheKeyInfo()
    {
        return array();
    }

    /**
     * @return string
     */
    protected function _getCacheKey()
    {
        $keyInfo = array_merge(
            array(
                'class'           => get_class($this),
                'template'        => $this->getTemplate(),
                'disable_wrapper' => $this->disable_wrapper,
                'tab_container'   => $this->tab_container,
                'title'           => $this->title,
                'class'           => $this->class,
                'url'             => $this->url,
                'locale'          => Axis_Locale::getLocale()->toString(),
                'site_id'         => Axis::getSiteId()
            ),
            $this->_getCacheKeyInfo()
        );

        $keyInfo = implode(',', $keyInfo);
        return md5($keyInfo);
    }

    /**
     * Retrieve the default configuration values of box.
     * Used at the backend box edit interface
     *
     * @return array
     */
    public function getConfigurationValues()
    {
        return array(
            'title'           => $this->_title,
            'class'           => $this->_class,
            'url'             => $this->_url,
            'disable_wrapper' => (int) $this->_disableWrapper,
            'tab_container'   => $this->_tabContainer,
            'template'        => $this->getTemplate()
        );
    }

    /**
     * Retrieve the box specific configuration fields.
     * Used at the beckend box edit interface
     *
     * @return array
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
