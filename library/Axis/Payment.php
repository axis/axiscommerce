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
 * @package     Axis_Payment
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Payment
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Payment
{
    /**
     *
     * @var array
     */
    private static $_methods;

    /**
     * Retrieve the list of paymetns methods of installed payment modules
     * @return array
     */
    public static function getMethodNames()
    {
        if ($methods = Axis::cache()->load('payment_methods_list')) {
            return $methods;
        }

        $prefix = 'Payment';
        $modules = Axis::app()->getModules();
        $methods = array();
        foreach($modules as $code => $path) {
            list($namespace, $moduleName) = explode('_', $code);
            if (substr($moduleName, 0, strlen($prefix)) != $prefix) {
                continue;
            }
            if (!is_dir($path . '/Model')) {
                continue;
            }
            $dir = opendir($path . '/Model');
            while ($fname = readdir($dir)) {
                if (!is_file("{$path}/Model/{$fname}")) {
                    continue;
                }
                list($methodName, $ext) = explode('.', $fname, 2);
                if ($ext != 'php' || $methodName == 'Abstract') {
                    continue;
                }
                $className = $code . '_Model_' . $methodName;
                if (!in_array('Axis_Method_Payment_Model_Abstract', class_parents($className))) {
                    continue;
                }
                $methods[$code . '/' . $methodName] = substr($moduleName, strlen($prefix)) . '_' . $methodName;
            }
            closedir($dir);
        }
        Axis::cache()->save($methods, 'payment_methods_list', array('modules'));
        return $methods;
    }

    /**
     * Retrieve array of installed payment methods
     *
     * @static
     * @return array
     */
    public static function getMethods()
    {
        $methods = array();
        foreach (self::getMethodNames() as $name) {
            $methods[$name] = self::getMethod($name);
        }
        return $methods;
    }

    /**
     * Retrieve payment method class
     *
     * @static
     * @param string $code
     * @return Axis_Method_Payment_Model_Abstract
     * @throws Axis_Exception
     */
    public static function getMethod($code)
    {
        if (!isset(self::$_methods[$code])) {
            $methodNames = self::getMethodNames();
            $modelAlias  = array_search($code, $methodNames);
            self::$_methods[$code] = Axis::model($modelAlias);
        }
        return self::$_methods[$code];
    }

    /**
     * Retrieve the list of allowed methods,
     * according to checkout process request
     *
     * @param array
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
        $checkout = Axis::single('checkout/checkout');
        $currentMethodCode = null;
        $methods = array();
        foreach (self::getMethods() as $method) {
            if (!$method->isEnabled() || !$method->isAllowed($request)) {
                continue;
            }
            if ($checkout->getPaymentMethodCode() == $method->getCode()) {
                $currentMethodCode = $method->getCode();
            }
            $methods[] = array(
                'code' => $method->getCode(),
                'title' => $method->getTitle(),
                'icon'  => $method->getIcon(),
                'sortOrder' => $method->config()->sortOrder
            );
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