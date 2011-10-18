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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Acl_Resource // @todo extends Axis_Core_Model_File_Collection
{
    /**
     *
     * @var array 
     */
    private $_resources;

    public function __construct()
    {
        if ($this->_resources = Axis::cache()->load('axis_acl_resources')) {
            return;
        }
        
        foreach (Axis::app()->getModules() as $moduleName => $path) {
            if ('Axis_Admin' === $moduleName) {
                $path = $path . '/controllers';
            } else  {
                $path = $path . '/controllers/Admin';
            }
            if (!is_dir($path)) {
                continue;
            }
            foreach($this->_scanDirectory($path) as $file) {

                if (strstr($file, "Controller.php" ) == false) {
                    continue;
                }
                include_once $file;
            }
        }
               
        $resource = 'admin';
        $resources = array($resource);
        $camelCaseToDash = new Zend_Filter_Word_CamelCaseToDash();
        foreach (get_declared_classes() as $class) { 
            if (!is_subclass_of($class, 'Axis_Admin_Controller_Back')) {
                continue;
            }
            list($module, $controller) = explode('Admin_', $class, 2);
            
            $module = rtrim($module, '_');
            if (empty($module)) {
                $module = 'Axis_Core';
            } elseif ('Axis' === $module) {
                $module = 'Axis_Admin';
            }
            $module = strtolower($camelCaseToDash->filter($module));
            list($namespace, $module) = explode('_', $module, 2);
            
            $resource .= '/' . $namespace;
            $resources[$resource] = $resource;
            
            $resource .= '/' . $module;
            $resources[$resource] = $resource;
            
            $controller = substr($controller, 0, strpos($controller, "Controller"));
            $controller = strtolower($camelCaseToDash->filter($controller));
            
            $resource .= '/' . $controller;
            $resources[$resource] = $resource;
            
            foreach(get_class_methods($class) as $action) {
                if (false == strstr($action, "Action")) {
                    continue;
                }
                $action = substr($action, 0, strpos($action, 'Action'));
                $action = strtolower($camelCaseToDash->filter($action));
//                $resources[$namespace][$module][$controller][] = $action;
                
                $resources[$resource . '/' . $action] = $resource . '/' . $action;
            }
            $resource = 'admin';
        }
        
        Axis::cache()->save(
            $resources, 'axis_acl_resources', array('modules')
        );
        $this->_resources = $resources;
    }
    
    protected function _scanDirectory($path) {
        $dir = dir($path);
        $files = array();
        while (false !== ($file = $dir->read())) {
            if ($file != '.' && $file != '..') {
                if (!is_link("$path/$file") && is_dir("$path/$file")) {
                    $files = array_merge($files, $this->_scanDirectory("$path/$file"));
                } else {
                    $files[] = "$path/$file";
                }
            }
        }
        $dir->close();

        return $files;
    }
    
    /**
     * @todo abstract toFlatTree 
     *
     * @return type 
     */
    public function toFlatTree() 
    {
        $data = array();
        foreach ($this->_resources as $resource) {
            
            if (false !== ($pos = strrpos($resource, '/'))) {
                $text   = substr($resource, $pos + 1);
                $parent = substr($resource, 0, $pos);
                
            } else {
                $text   = $resource; 
                $parent = false;
            }
            
            $data[$resource] = array(
                'id'     => $resource,
                'text'   => $text,
                'parent' => $parent,
                'leaf'   => true
            );
            if (isset($data[$parent])) {
                $data[$parent]['leaf'] = false;
            }
        }
            
        return $data;
    }
    
//    public function fetchAll() 
//    {
//        return $this->_resources;
//    }
}