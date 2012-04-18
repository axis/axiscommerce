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

/* load base config */
if (class_exists('Axis')) {
    $root = Axis::config()->system->path;
} else {
    $root = rtrim(str_replace('scripts', '', dirname(realpath(__FILE__))), '/\\');
}
require $root . '/app/etc/config.php';
/*
 * set optimal directory order for __autoload  function
 */
set_include_path(
    $config['system']['path'] . '/library'  . PATH_SEPARATOR
  . $config['system']['path'] . '/app/code' . PATH_SEPARATOR
  . get_include_path()
);



/*
 * Autoload function
 */
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace(array('Axis'));

/*
 * Init config
 */
$config = new Axis_Config($config, true);

/**
 * init database
 */
$db = Zend_Db::factory('Pdo_Mysql', array(
    'host'      => $config->db->host,
    'username'  => $config->db->username,
    'password'  => $config->db->password,
    'dbname'    => $config->db->dbname,
    'driver_options'=> array(
        //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
        1002 => 'SET NAMES UTF8'
    )
));

Zend_Db_Table_Abstract::setDefaultAdapter($db);
//Axis_Config::setDefaultDbAdapter($db);

/*
 * Init cache system
 */
$frontendOptions = array(
   'lifetime' => 7200,
   'automatic_serialization' => true
);
$backendOptions = array(
    'cache_dir' => $root . '/var/cache/'
);
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

Zend_Registry::set('config', $config);
Zend_Registry::set('db', $db);
Zend_Registry::set('cache', $cache);