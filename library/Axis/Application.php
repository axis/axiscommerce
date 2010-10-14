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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

@include_once 'Zend/Application.php';
/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Application extends Zend_Application
{
    /**
     * Checks is Axis is installed already
     *
     * @static
     * @return bool
     */
    public static function isInstalled()
    {
        return file_exists(AXIS_ROOT . '/app/etc/config.php');
    }

    /**
     * Retrieve the list of active, installed modules
     *
     * @return array code => path pairs
     */
    public function getModules()
    {
        if (Zend_Registry::isRegistered('modules')) {
            return Zend_Registry::get('modules');
        }
        if (!$modules = Axis::cache()->load('modules_list')) {
            $list = Axis::single('core/module')->getList('is_active = 1');
            $result = array();
            foreach ($list as $moduleCode => $values) {
                list($namespace, $module) = explode('_', $moduleCode, 2);
                $modules[$moduleCode] = Axis::config()->system->path
                    . '/app/code/' . $namespace . '/' . $module;
            }
            Axis::cache()->save($modules, 'modules_list', array('modules'));
        }
        Zend_Registry::set('modules', $modules);
        return Zend_Registry::get('modules');
    }

    /**
     * Retrieve the controllers paths
     *
     * @return array code => path pairs
     */
    public function getControllers()
    {
        if (!$result = Axis::cache()->load('controllers_list')) {
            $modules = $this->getModules();
            $result = array();
            foreach ($modules as $moduleCode => $path) {
                if (is_readable($path . '/controllers')) {
                    $result[$moduleCode] = $path . '/controllers';
                }
            }
            Axis::cache()->save($result, 'controllers_list', array('modules'));
        }
        return $result;
    }

    /**
     * Retrieve array of paths to route files
     *
     * @return array
     */
    public function getRoutes()
    {
        if (!($routes = Axis::cache()->load('routes_list'))) {
            $modules = $this->getModules();
            $routes = array();
            foreach ($modules as $moduleCode => $path) {
                if (file_exists($path . '/etc/routes.php')
                    && is_readable($path . '/etc/routes.php')) {

                    $routes[] = $path . '/etc/routes.php';
                }
            }
            Axis::cache()->save(
                $routes, 'routes_list', array('modules')
            );
        }
        return $routes;
    }

    /**
     *
     * @return array
     */
    public function getNamespaces()
    {
        $namespaces = array();
        $codePath = AXIS_ROOT . '/app/code';
        try {
            $codeDir = new DirectoryIterator($codePath);
        } catch (Exception $e) {
            throw new Axis_Exception(
                Axis::translate('core')->__(
                    "Directory %s not readable", $codePath
                )
            );
        }
        foreach ($codeDir as $namespace) {
            $namespace = $namespace->getFilename();
            if ($namespace[0] == '.') {
                continue;
            }
            $namespaces[] = $namespace;
        }
        return $namespaces;
    }

    /**
     * Return current Axis version
     *
     * @return string
     */
    public function getVersion()
    {
        return '0.7';
    }
}