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
 * @package     Axis_Shipping
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Shipping
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Shipping
{
    /**
     * @static
     * @var array
     */
    private static $_methods;

    /**
     * Retrieve the list of shipping methods of installed shipping modules
     *
     * @static
     * @return array
     */
    public static function getMethodNames()
    {
        if ($methods = Axis::cache()->load('shipping_methods_list')) {
            return $methods;
        }

        $prefix = 'Shipping';
        $modules = Axis::app()->getModules();
        $methods = array();
        foreach($modules as $path) {
            $moduleName = str_replace(
                Axis::config()->system->path . '/app/code/Axis/', '', $path
            );
            if (substr($moduleName, 0, strlen($prefix)) != $prefix) {
                continue;
            }
            $dir = opendir($path . '/Model');
            while ($fname = readdir($dir)) {
                if (!is_file("{$path}/Model/{$fname}")) {
                    continue;
                }
                list($methodName, $ext) = explode('.', $fname, 2);
                if ($ext != 'php') {
                    continue;
                }
                $methods[] = substr($moduleName, strlen($prefix)) . '_' . $methodName;
            }
            closedir($dir);
        }
        Axis::cache()->save(
            $methods, 'shipping_methods_list', array('modules')
        );
        return $methods;
    }

    /**
     * Retrieve array of installed shipping methods
     *
     * @static
     * @return array
     */
    public static function getMethods()
    {
        $methods = array();
        foreach (self::getMethodNames() as $name) {
            $method = self::getMethod($name);
            $methods[$method->getCode()] = $method;
        }
        return $methods;
    }

    /**
     * Retrieve shipping method class
     *
     * @param string $code
     * @return Axis_Method_Shipping_Model_Abstract|null
     */
    public static function getMethod($code)
    {
        if (isset(self::$_methods[$code])) {
            return self::$_methods[$code];
        }

        list($moduleName, $methodName) = explode('_', $code, 2);

        $methodType = null;
        if (strstr($methodName, '_')) {
            list($methodName, $methodType) = explode('_', $methodName, 2);
        }
        $className = 'Axis_Shipping' . $moduleName . '_Model_' . $methodName;
        self::$_methods[$code] = new $className($methodType);
        return self::$_methods[$code];
    }

    /**
     * Retrieve the list of allowed methods,
     * according to checkout process request
     *
     * @param array $request
     * @static
     * @return array('methods' => array(), 'currentMethodCode' => string)
     */
    public static function getAllowedMethods($request)
    {
        if (!function_exists('_sortOrder')) {
            function _sortOrder($a, $b)
            {
                if ($a['sortOrder'] == $b['sortOrder']) {
                    return 0;
                }
                return $a['sortOrder'] < $b['sortOrder'] ? -1 : 1;
            }
        }
        $checkout =  Axis::single('checkout/checkout');
        $currentMethodCode = null;
        $methods = array();

        foreach (self::getMethods() as $method) {
            if (!$method->isEnabled() || !$method->isAllowed($request)) {
                continue;
            }

            foreach ($method->getAllowedTypes($request) as $type) {
                if ($checkout->getShippingMethodCode() == $type['id']) {
                    $currentMethodCode = $type['id'];
                }
                $methods[$method->getCode()][] = $type;
            }
            $methods[$method->getCode()]['sortOrder'] = $method->config()->sortOrder;
        }
        uasort($methods, '_sortOrder');

        foreach ($methods as &$method) {
            unset($method['sortOrder']);
        }

        return array (
            'methods'           => $methods,
            'currentMethodCode' => $currentMethodCode
        );
    }
}