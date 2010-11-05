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
class Axis_Controller_Router_Rewrite extends Zend_Controller_Router_Rewrite
{
    const PRIORITY_PERIOD = 100;

    /**
     *
     * @var array 
     */
    protected $_priorities = array();

    public function addDefaultRoutes()
    {
        if (!$this->hasRoute('default')) {
            $dispatcher = $this->getFrontController()->getDispatcher();
            $request = $this->getFrontController()->getRequest();
            
            $compat = new Axis_Controller_Router_Route_Module(array(), $dispatcher, $request);
            $this->_routes = array_merge(array('default' => $compat), $this->_routes);
        }
    }

    /**
     * Add route to the route chain
     *
     * If route contains method setRequest(), it is initialized with a request object
     *
     * @param  string                                 $name       Name of the route
     * @param  Zend_Controller_Router_Route_Interface $route      Instance of the route
     * @param  mixed                                  $priority
     * @return Zend_Controller_Router_Rewrite
     */
    public function addRoute($name, Zend_Controller_Router_Route_Interface $route, $priority = null)
    {
        if (method_exists($route, 'setRequest')) {
            $route->setRequest($this->getFrontController()->getRequest());
        }

        if (null === $priority) {
            if (empty($this->_priorities)) {
                $priority = 0;
            } else {
                $priority = max(array_keys($this->_priorities)) + self::PRIORITY_PERIOD;
            }
        }

        $this->_routes[$name] = $route;
        $this->_priorities[$priority] = $name;

        return $this;
    }

    public function sortRoutes()
    {
        $priorities = array();
        $flip = array_flip($this->_priorities);
        
        foreach ($this->_priorities as $priority => $name) {
            if (is_string($priority) && isset($flip[$priority])) {
                for ($index = 1; $index < self::PRIORITY_PERIOD - 1; $index++) {
                    $priority = $flip[$priority] + $index;
                    if (isset($priorities[$priority])) {
                        continue;
                    }
                    break;
                }
            }
            $priorities[$priority] = $name;
        }

        ksort($priorities);
        $routes = array();
        foreach ($priorities as $priority => $name) {
            $routes[$name] = $this->_routes[$name];
        }
        return $this->_routes = $routes;
    }
}