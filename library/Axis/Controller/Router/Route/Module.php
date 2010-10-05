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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2010 Axis
 * @copyright   Dmitry Merva  http://myengine.com.ua  d.merva@gmail.com
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Route
 * @author      Axis Core Team <core@axiscommerce.com>
 */
 class Axis_Controller_Router_Route_Module extends Zend_Controller_Router_Route_Module
{
    /**
     *
     * @static
     * @var const array
     */
    protected static $_locales = array('en', 'ru');

    /**
     *
     * @static
     * @var string
     */
    protected static $_defaultLocale = 'en';     

    /**
     * @static
     * @param array $locales
     */
    public static function setLocales(array $locales)
    {
        self::$_locales = $locales;
    }

    /**
     *
     * @static
     * @param string $locale
     */
    public static function setDefaultLocale($locale) 
    {
        self::$_defaultLocale = $locale;
    }   
    
    /**
     * Set request keys based on values in request object
     *
     * @return void
     */
    protected function _setRequestKeys()
    {
        if (null !== $this->_request) {
            $this->_moduleKey     = $this->_request->getModuleKey();
            $this->_controllerKey = $this->_request->getControllerKey();
            $this->_actionKey     = $this->_request->getActionKey();
        }

        if (null !== $this->_dispatcher) {
            $this->_defaults += array(
                'locale'              => self::$_defaultLocale,
                $this->_controllerKey => $this->_dispatcher->getDefaultControllerName(),
                $this->_actionKey     => $this->_dispatcher->getDefaultAction(),
                $this->_moduleKey     => $this->_dispatcher->getDefaultModule()
            );
        }

        $this->_keysSet = true;
    }    
    
    /**
     * Matches a user submitted path. Assigns and returns an array of variables
     * on a successful match.
     *
     * If a request object is registered, it uses its setModuleName(),
     * setControllerName(), and setActionName() accessors to set those values.
     * Always returns the values as an array.
     *
     * @param string Path used to match against this routing map
     * @return array An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        $this->_setRequestKeys();

        $values = array();
        $params = array();
        $path   = trim($path, self::URI_DELIMITER);

        if ($path != '') {

            $path = explode(self::URI_DELIMITER, $path);
            
            if (count($path) && !empty($path[0]) && in_array($path[0], self::$_locales)) {
                $values['locale'] = array_shift($path);
            }
            
            if (count($path) && $this->_dispatcher && $this->_dispatcher->isValidModule($path[0])) {
                $values[$this->_moduleKey] = array_shift($path);
                $this->_moduleValid = true;
            }

            if (count($path) && !empty($path[0])) {
                $values[$this->_controllerKey] = array_shift($path);
            }

            if (count($path) && !empty($path[0])) {
                $values[$this->_actionKey] = array_shift($path);
            }

            $numSegs = count($path);
            if ($numSegs) {
                for ($i = 0; $i < $numSegs; $i = $i + 2) {
                    $key = urldecode($path[$i]);
                    $val = isset($path[$i + 1]) ? urldecode($path[$i + 1]) : null;
                    $params[$key] = $val;
                }
            }
        }

        $this->_values = $values + $params;

        return $this->_values + $this->_defaults;
    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route
     *
     * @param array An array of variable and value pairs used as parameters
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false)
    {
        if (!$this->_keysSet) {
            $this->_setRequestKeys();
        }

        $params = (!$reset) ? $this->_values : array();

        foreach ($data as $key => $value) {
            if (null !== $value) {
                $params[$key] = $value;
            } elseif (isset($params[$key])) {
                unset($params[$key]);
            }
        }

        $params += $this->_defaults;
        
        $url = '';

        if ($this->_moduleValid || array_key_exists($this->_moduleKey, $data)) {
            $module = $params[$this->_moduleKey];
        }
        unset($params[$this->_moduleKey]);

        $controller = $params[$this->_controllerKey];
        unset($params[$this->_controllerKey]);

        $action = $params[$this->_actionKey];
        unset($params[$this->_actionKey]);

        $locale = $params['locale'];
        unset($params['locale']);
        
        
        foreach ($params as $key => $value) {
            $url .= '/' . $key;
            $url .= '/' . $value;
        }

        if (!empty($url) || $action !== $this->_defaults[$this->_actionKey]) {
            $url = '/' . $action . $url;
        }

        if (!empty($url) || $controller !== $this->_defaults[$this->_controllerKey]) {
            $url = '/' . $controller . $url;
        }

        if (isset($module) && (!empty($url) || $module !== $this->_defaults[$this->_moduleKey])) {
            $url = '/' . $module . $url;
        }

        if ($locale !== $this->_defaults['locale']) {
            $url = '/' . $locale . $url;
        }
        return ltrim($url, self::URI_DELIMITER);
    }

}
