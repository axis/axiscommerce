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
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_AclResourceController extends Axis_Admin_Controller_Back
{
    public function listAction() 
    {
        function scanDirectory($path) {
            $dir = dir($path);
            $files = array();
            while (false !== ($file = $dir->read())) {
                if ($file != '.' && $file != '..') {
                    if (!is_link("$path/$file") && is_dir("$path/$file")) {
                        $files = array_merge($files, scanDirectory("$path/$file"));
                    } else {
                        $files[] = "$path/$file";
                    }
                }
            }
            $dir->close();
            
            return $files;
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
            foreach(scanDirectory($path) as $file) {

                if (strstr($file, "Controller.php" ) == false) {
                    continue;
                }
                include_once $file;
            }
        }
        
        $camelCaseToDash = new Zend_Filter_Word_CamelCaseToDash();
        $acl = array();
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
            
            $controller = substr($controller, 0, strpos($controller, "Controller"));
            $controller = strtolower($camelCaseToDash->filter($controller));
            foreach(get_class_methods($class) as $action) {
                if (false == strstr($action, "Action")) {
                    continue;
                }
                $action = substr($action, 0, strpos($action, 'Action'));
                $action = strtolower($camelCaseToDash->filter($action));
                $acl[$namespace][$module][$controller][] = $action;
            }
        }      
        
        $data = array();
        $resource = array('admin');
        
        $data[] = array(
            'id'     => 'admin',
            'text'   => 'admin',
//            'permission' => 'parent',
            'parent' => false,
            'leaf'   => false
        );
        foreach ($acl as $namespace => $modules) {
            $parent = implode('/', $resource);
            array_push($resource, $namespace);
            $data[] = array(
                'id'     => implode('/', $resource),
                'text'   => $namespace,
                'parent' => $parent,
//                'permission' => 'parent',
                'leaf'   => false
            );
            foreach ($modules as $module => $controllers) {
                $parent = implode('/', $resource);
                array_push($resource, $module);
                $data[] = array(
                    'id'     => implode('/', $resource),
                    'text'   => $module,
                    'parent' => $parent,
//                    'permission' => 'parent',
                    'leaf'   => false
                );
                foreach ($controllers as $controller => $actions) {
                    $parent = implode('/', $resource);
                    array_push($resource, $controller);
                    $data[] = array(
                        'id'     => implode('/', $resource),
                        'text'   => $controller,
                        'parent' => $parent,
//                        'permission' => 'parent',
                        'leaf'   => false
                    );
                    foreach ($actions as $action) {
                        $parent = implode('/', $resource);
                        array_push($resource, $action);
                        $data[] = array(
                            'id'     => implode('/', $resource),
                            'text'   => $action,
                            'parent' => $parent,
//                            'permission' => 'parent',
                            'leaf'   => true
                        );
                        array_pop($resource);
                    }
                    array_pop($resource);
                }
                array_pop($resource);
            }
            array_pop($resource);
        }
        return $this->_helper->json->sendRaw($data);
    }
}