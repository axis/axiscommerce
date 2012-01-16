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
 * @subpackage  Axis_Controller_Action_Helper
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Action_Helper_Breadcrumbs extends Zend_Controller_Action_Helper_Abstract
{
    /**
     *
     * @var Zend_Navigation_Container
     */
    protected $_container;


    /**
     * Hook into action controller initialization
     *
     * @return void
     */
    public function init() 
    {
        $this->_container = new Zend_Navigation();
    }
    
    /**
     *
     * @param array $page
     * @return Axis_Controller_Action_Helper_Breadcrumbs 
     */
    public function add(array $page) 
    {
        $container = $this->_container;

        $iterator = new RecursiveIteratorIterator($container,
                RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $_page) {
            $container = $_page;
        }

        $page['active'] = true;
        $container->addPage($page);
        return $this;
    }
    
    /**
     * Strategy pattern; call object as method
     *
     * @param array $page
     * @return Axis_Controller_Action_Helper_Breadcrumbs
     */
    public function direct(array $page)
    {
        return $this->add($page);
    }
    
    /**
     * Proxy method calls to breadcrumb container object
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->_container, $method)) {
            return call_user_func_array(array($container, $method), $args);
        }

        throw new Axis_Exception(sprintf("Invalid method '%s' called on breadcrumbs action helper", $method));
    }
    
    /**
     *
     * @return Zend_Navigation_Container 
     */
    public function getContainer() 
    {
        return $this->_container;
    }
}
