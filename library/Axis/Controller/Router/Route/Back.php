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
class Axis_Controller_Router_Route_Back extends Zend_Controller_Router_Route
{
    /**
     * Prepares the route for mapping by splitting (exploding) it
     * to a corresponding atomic parts. These parts are assigned
     * a position which is later used for matching and preparing values.
     *
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     * @param array $reqs Regular expression requirements for variables (keys as variable names)
     * @param Zend_Translate $translator Translator to use for this instance
     */
    public function __construct($route, $defaults = array(), $reqs = array(), Zend_Translate $translator = null, $locale = null)
    {
        $route = Axis::config('core/backend/route') . $this->_urlDelimiter . $route;
        parent::__construct($route, $defaults, $reqs, $translator, $locale);
    }
    
    /**
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path, $partial = false)
    {
        $params = parent::match($path, $partial);
        if (!empty($params['controller'])
            && 'Axis_Admin' !== $params['module']) {
            
            $params['controller'] = 'admin_' . $params['controller'];
        }

        if ($params) {
            Axis_Area::backend();
        }
        return $params;
    }
}