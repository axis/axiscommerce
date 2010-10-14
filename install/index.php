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
 * @package     Axis_Install
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

define('AXIS_ROOT', realpath('../'));

set_include_path(
  realpath('../app/code') . PATH_SEPARATOR
  . realpath('../library') . PATH_SEPARATOR
  . get_include_path()
);

@include_once 'Axis/Install/Model/Translate.php';

@include_once 'Zend/Loader/Autoloader.php';
if (!class_exists('Zend_Loader')) {
    echo 'Please, copy Zend Framework to the "library" folder: '
        . realpath('../library');
    exit();
}
$autoloadeer = Zend_Loader_Autoloader::getInstance();
$autoloadeer->setFallbackAutoloader(true);

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
        (getenv('APPLICATION_ENV') ?
            getenv('APPLICATION_ENV') : 'production'
        )
    );

$displayErrors = (int)(APPLICATION_ENV !== 'development');

$bootstrapConfig = array(
    'bootstrap' => array(
        'path' => AXIS_ROOT . '/library/Axis/Bootstrap/Install.php',
        'class' => 'Axis_Bootstrap_Install'
    ),
    'phpSettings' => array(
        'display_startup_errors' => $displayErrors,
        'display_errors' => $displayErrors
    )
);

$application = new Axis_Application(APPLICATION_ENV, $bootstrapConfig);

if (is_readable(AXIS_ROOT . '/app/etc/config.php')) {
    $application->bootstrap('DbAdapter');
}
$application->bootstrap(array('Loader', 'View', 'Session', 'FrontController'));