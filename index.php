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
 * Index file
 *
 * @category    Axis
 * @package     Axis_Core
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

if (true === version_compare(phpversion(), '5.2.4', '<')) {
    echo 'Update your PHP version to 5.2.4 or newer. Current PHP version: ' . phpversion();
    exit;
}
define('AXIS_ROOT', realpath(dirname(__FILE__)));

if (!file_exists('./app/etc/config.php')) {
    if (!headers_sent()) {
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'install';
        header("Location: http://$host$uri/$extra/");
    }
    exit;
}

set_include_path(
    realpath('library')
    . PATH_SEPARATOR . realpath('app/code')
//    . PATH_SEPARATOR . get_include_path() // commented to fix open_basedir restriction. See Zend_Loader::isReadable()
);

include_once 'Axis/Application.php';

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
        (getenv('APPLICATION_ENV') ?
            getenv('APPLICATION_ENV') : 'production'
        )
    );

$displayErrors = (int)(APPLICATION_ENV === 'development');

$bootstrapConfig = array(
    'bootstrap' => array(
        'path' =>'Axis/Bootstrap.php',
        'class' => 'Axis_Bootstrap'
    ),
    'phpSettings' => array(
        'display_startup_errors' => $displayErrors,
        'display_errors' => $displayErrors
    )
);

$application = new Axis_Application(APPLICATION_ENV, $bootstrapConfig);

$application->bootstrap()->run();