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
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Router
 * @copyright   Copyright 2008-2011 Axis
 * @copyright   Dmitry Merva  http://myengine.com.ua  d.merva@gmail.com
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Router
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Router_Rewrite extends Zend_Controller_Router_Rewrite
{
    /**
     *
     * @var array
     */
    protected $_dependency = array();

    /**
     * Add route to the route chain
     *
     * If route contains method setRequest(), it is initialized with a request object
     *
     * @param  string                                 $name       Name of the route
     * @param  Zend_Controller_Router_Route_Interface $route      Instance of the route
     * @param  string                                 $before
     * @return Zend_Controller_Router_Rewrite
     */
    public function addRoute($name, Zend_Controller_Router_Route_Interface $route, $before = null)
    {
        if (method_exists($route, 'setRequest')) {
            $route->setRequest($this->getFrontController()->getRequest());
        }

        $this->_routes[$name] = $route;

        if (null !== $before) {
            $this->_dependency[$name] = $before;
        }
        return $this;
    }

    /**
     * "When your power eclipses mine I will become expendable.
     *  This is the Rule of Two: one Master and one apprentice.
     *  When you are ready to claim the mantle of Dark Lord as your own, you must do so by eliminating me."
     * ― Darth Bane
     *
     * @return array
     */
    public function sortRoutes()
    {

        foreach ($this->_dependency as $afterRoute => $beforeRoute) {
            $replacement = $this->_routes[$afterRoute];
            unset($this->_routes[$afterRoute]);
            $offset = array_search($beforeRoute, array_keys($this->_routes)) + 1;

            $this->_routes = array_slice($this->_routes, 0, $offset, true)
                + array($afterRoute => $replacement)
                + array_slice($this->_routes, $offset, NULL, true);
        }
        return $this->_routes;
    }
    
    /**
     * Find a matching route to the current PATH_INFO and inject
     * returning values to the Request object.
     *
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Request_Abstract Request object
     */
    public function route(Zend_Controller_Request_Abstract $request)
    {
        //sort routes
        $this->sortRoutes();
        return parent::route($request);
    }
}