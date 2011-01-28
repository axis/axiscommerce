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
 * @package     Axis_Test
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */
define('AXIS_ROOT', realpath(dirname('../')));

define('TESTS_PATH', realpath(dirname(__FILE__)));
set_include_path(
    realpath(TESTS_PATH . '/../library')  . PATH_SEPARATOR
  . realpath(TESTS_PATH . '/../app/code') . PATH_SEPARATOR
  . realpath(TESTS_PATH) . PATH_SEPARATOR
  . get_include_path()
);

@include_once 'Zend/Loader/Autoloader.php';
if (!class_exists('Zend_Loader')) {
    die(
        'Please, copy Zend Framework to the "library" folder: '
        . realpath(TESTS_PATH . '/../library')
    );
}

$autoloadeer = Zend_Loader_Autoloader::getInstance();
$autoloadeer->setFallbackAutoloader(true);

require_once TESTS_PATH . '/config.php';
Zend_Registry::set('config', $config);

Axis::$siteId = $config['system']['siteId'];

$bootstrapConfig = array(
    'bootstrap' => array(
        'path'  => AXIS_ROOT . '/library/Axis/Bootstrap/Test.php',
        'class' => 'Axis_Bootstrap_Test'
    ),
    'phpSettings' => array(
        'display_startup_errors' => true,
        'display_errors'         => true
    )
);

$application = new Zend_Application(APPLICATION_ENV, $bootstrapConfig);

$application->bootstrap(
    array('Loader', 'Config', 'DbAdapter', 'Cache', 'Locale', 'Area', 'Translate')
);