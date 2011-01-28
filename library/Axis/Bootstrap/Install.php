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
 * @package     Axis_Bootstrap
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Bootstrap
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Bootstrap_Install extends Axis_Bootstrap
{
    protected function _initLoader()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace(array(
            'Axis'
        ));
        return $autoloader;
    }

    protected function _initLayout()
    {
        return Zend_Layout::startMvc();
    }

    protected function _initSession()
    {
        $cacheDir = AXIS_ROOT . '/var/sessions';
        if (!is_readable($cacheDir)) {
            mkdir($cacheDir, 0777);
        } elseif (!is_writable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
        if (!is_writable($cacheDir)) {
            echo "Sessions directory should be writable. Run 'chmod -R 0777 path/to/var'";
            exit();
        }
        Zend_Session::start(array(
            'cookie_lifetime' => 864000, // 10 days
            'name'            => 'axisid',
            'strict'          => 'off',
            'save_path'       => $cacheDir
        ));
        return Axis::session('install');
    }

    protected function _initCache()
    {
        $frontendOptions = array(
            'lifetime'                  => 864000,
            'automatic_serialization'   => true
        );
        $cacheDir = AXIS_ROOT . '/var/cache';
        if (!is_readable($cacheDir)) {
            mkdir($cacheDir, 0777);
        } elseif(!is_writable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
        if (!is_writable($cacheDir)) {
            echo "Cache directory should be writable. Run 'chmod -R 0777 path/to/var'";
            exit();
        }
        $backendOptions = array(
            'cache_dir'                 => $cacheDir,
            'hashed_directory_level'    => 1,
            'file_name_prefix'          => 'axis_cache',
            'hashed_directory_umask'    => 0777
        );
        Zend_Registry::set('cache', Zend_Cache::factory(
            'Core', 'Zend_Cache_Backend_File',
            $frontendOptions,
            $backendOptions,
            false,
            true
        ));

        return Axis::cache();
    }

    protected function _initLocale()
    {
        $session  = Axis::session('install');
        $timezone = Axis_Locale::DEFAULT_TIMEZONE;
        $locale   = Axis_Locale::DEFAULT_LOCALE;

        if (is_array($session->locale)) {
            $timezone = current($session->locale['timezone']);
        }
        if ($session->current_locale) {
            $locale = $session->current_locale;
        }

        Axis_Locale::setLocale($locale);
        Axis_Locale::setTimezone($timezone);
    }

    protected function _initArea()
    {
        Zend_Registry::set('area', 'install');
    }

    protected function _initFrontController()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->throwExceptions(true);
        $front->setParam('noViewRenderer', true);
        $front->setControllerDirectory('app/controllers');
        $front->dispatch();
        return $front;
    }
}