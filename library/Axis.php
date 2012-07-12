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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis
{
    /**
     * Retrieve parent application instance
     *
     * @static
     * @return Axis_Application
     */
    public static function app()
    {
        return Zend_Registry::get('app');
    }

    /**
     * Return current site
     * @return Axis_DB_Table_Row
     */
    public static function getSite()
    {
        if (!Zend_Registry::isRegistered('core/current_site')) {
            $host  = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
            $sheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off") ? 'https' : 'http';
            $port  = (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80);
            $uri   = $sheme . '://' . $host;
            if ((('http' == $sheme) && (80 != $port))
                || (('https' == $sheme) && (443 != $port))) {

                $uri .= ':' . $port;
            }
            $uri .= (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');

            $mSite = self::single('core/site');
            if (!($site = $mSite->getByUrl($uri)) && !($site = $mSite->fetchRow())) {
                throw new Axis_Exception(
                    Axis::translate('core')->__(
                        "There is no site linked with url %s" , $uri
                ));
            }
            Zend_Registry::set('core/current_site', $site);
        }
        return Zend_Registry::get('core/current_site');
    }

    /**
     * Return current site id
     *
     * @static
     * @return int
     */
    public static function getSiteId()
    {
        return self::getSite()->id;
    }

    /**
     * Return customer id or null
     *
     * @static
     * @return mixed (int|null)
     */
    public static function getCustomerId()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return null;
        }
        return Zend_Auth::getInstance()->getIdentity();
    }

    /**
     * Returns customer row if logged in or null if not
     *
     * @static
     * @return Axis_Account_Model_Customer_Row|null
     */
    public static function getCustomer()
    {
        if (!$customerId = self::getCustomerId()) {
            return null;
        }
        if (!Zend_Registry::isRegistered('account/current_customer')) {
            $customer = Axis::model('account/customer')
                ->find($customerId)
                ->current();

            if (!$customer) {
                return null; // invalid customerId
            }
            Zend_Registry::set('account/current_customer', $customer);
        }
        return Zend_Registry::get('account/current_customer');
    }

    /**
     * Returns singleton object
     *
     * @static
     * @param string $class
     * @param array $arguments [optional]
     * @return Axis_Db_Table_Abstract
     */
    public static function single($class, $arguments = array())
    {
        $class = self::getClass($class);

        if (!Zend_Registry::isRegistered($class)) {
            $instance = new $class($arguments);
            Zend_Registry::set($class, $instance);
        }
        return Zend_Registry::get($class);
    }

    /**
     * Return requested model instance
     *
     * @static
     * @param string $model
     * @param array $arguments class arguments
     * @return Axis_Db_Table_Abstract
     */
    public static function model($model, $arguments = array())
    {
        $class = self::getClass($model);

        return new $class($arguments);
    }

    /**
     * Return class name by shortname
     *
     * @static
     * @param string $name
     * @param string $type
     * @return string
     */
    public static function getClass($name, $type = 'Model')
    {
        $parts = explode('/', $name);

        if (1 === count($parts)) {
            return $name;
        }

        if (strstr($parts[0], '_')) {
            list($namespace, $module) = explode('_', $parts[0]);
            $namespace = ucfirst($namespace);
        } else {
            $namespace = 'Axis';
            $module   = $parts[0];
        }
        $module = ucfirst($module);
        $name   = str_replace(' ', '_', ucwords(str_replace('_', ' ', $parts[1])));

        return $namespace . '_' . $module . '_' . $type . '_' . $name;
    }

    /**
     * Retrieve config object or config value,
     * if name is requested
     *
     * @static
     * @param string $name[optional] config value to load
     * @param string $default[optional] default value to return
     * @return object Axis_Config|mixed
     */
    public static function config($name = null, $siteId = null, $default = null)
    {
//        if (!Zend_Registry::isRegistered('config')) {
//            throw new Axis_Exception(
//                Axis::translate('core')->__(
//                    'Config is not initialized'
//                )
//            );
//        }
        if (null !== $name) {
            return Zend_Registry::get('config')->get($name, $siteId, $default);
        }
        return Zend_Registry::get('config');
    }

    /**
     * Retrieve database adapter object
     *
     * @static
     * @return Zend_Db_Adapter_Abstract
     */
    public static function db()
    {
        return Zend_Registry::get('db');
    }

    /**
     * Retrieve session object
     *
     * @static
     * @return Zend_Session_Namespace
     */
    public static function session($namespace = 'Core')
    {
        $namespace = 'Axis_' . $namespace;
        if (!Zend_Registry::isRegistered($namespace)) {
            Zend_Registry::set($namespace, new Zend_Session_Namespace($namespace));
        }
        return Zend_Registry::get($namespace);
    }

    /**
     * Retrieve cache object
     *
     * @static
     * @return Zend_Cache_Core
     */
    public static function cache()
    {
        return Zend_Registry::get('cache');
    }

    /**
     * Retrieve Axis_Message object
     *
     * @static
     * @return Axis_Message
     */
    public static function message($namespace = 'messenger')
    {
        return Axis_Message::getInstance($namespace);
    }

    /**
     * Dispatch event
     *
     * Calls all of the methods linked to dispatched event
     *
     * @static
     * @param string $name
     * @param array $data [optional]
     * @return Axis_Event_Observer
     */
    public static function dispatch($name, $data = array())
    {
        return Axis_Event_Observer::getInstance()->dispatch($name, $data);
    }

    /**
     *
     * @param string $name
     * @return Axis_Translate
     */
    public static function translate($module = 'Axis_Core')
    {
        if (false === strpos($module, '_')) {
            $module = 'Axis' . '_' . ucfirst($module);
        }
        $module = str_replace(' ', '_', ucwords(str_replace('_', ' ', $module)));
        return Axis_Translate::getInstance($module);
    }

    /**
     * Retrieve locale object
     *
     * @static
     * @return Zend_Locale
     */
    public static function locale()
    {
        return Zend_Registry::get('Zend_Locale');
    }
}