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
 * @uses        Zend_Application_Bootstrap_Bootstrap
 * @category    Axis
 * @package     Axis_Bootstrap
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initEnviroment()
    {
        date_default_timezone_set('UTC');
        error_reporting(E_ALL | E_STRICT);
        /**
         * Custom error handler E_ALL to Exception
         *
         * @param int $level
         * @param string $message
         * @param string $file
         * @param int $line
         */
        function AxisErrorHandler($level, $message, $file, $line)
        {
            $level = $level & error_reporting();

            if ($level == 0) {
                return false;
            }

            if (!defined('E_STRICT')) {
                define('E_STRICT', 2048);
            }
            if (!defined('E_RECOVERABLE_ERROR')) {
                define('E_RECOVERABLE_ERROR', 4096);
            }
            if (!defined('E_DEPRECATED')) {
                define('E_DEPRECATED', 8192);
            }
            if (!defined('E_USER_DEPRECATED')) {
                define('E_USER_DEPRECATED', 16384);
            }

            $errorMessage = '';

            switch($level){
                case E_ERROR:
                    $errorMessage .= 'Error';
                    break;
                case E_WARNING:
                    $errorMessage .= 'Warning';
                    break;
                case E_PARSE:
                    $errorMessage .= 'Parse Error';
                    break;
                case E_NOTICE:
                    $errorMessage .= 'Notice';
                    break;
                case E_CORE_ERROR:
                    $errorMessage .= 'Core Error';
                    break;
                case E_CORE_WARNING:
                    $errorMessage .= 'Core Warning';
                    break;
                case E_COMPILE_ERROR:
                    $errorMessage .= 'Compile Error';
                    break;
                case E_COMPILE_WARNING:
                    $errorMessage .= 'Compile Warning';
                    break;
                case E_USER_ERROR:
                    $errorMessage .= 'User Error';
                    break;
                case E_USER_WARNING:
                    $errorMessage .= 'User Warning';
                    break;
                case E_USER_NOTICE:
                    $errorMessage .= 'User Notice';
                    break;
                case E_STRICT:
                    $errorMessage .= 'Strict Notice';
                    break;
                case E_RECOVERABLE_ERROR:
                    $errorMessage .= 'Recoverable Error';
                    break;
                case E_DEPRECATED:
                    $errorMessage .= 'Deprecated functionality';
                    break;
                case E_USER_DEPRECATED:
                    $errorMessage .= 'User-generated warning message';
                    break;
                default:
                    $errorMessage .= "Unknown error ($level)";
                    break;
            }
            
            $errorMessage .= ": {$message}  in {$file} on line {$line}";
            throw new Exception($errorMessage);
        }
        set_error_handler('AxisErrorHandler');
//
//        function AxisFatalErrorHandler()
//        {
//            $error = error_get_last();
//            if ($error['type'] == E_ERROR || $error['type'] == E_CORE_ERROR
//                || $error['type'] == E_COMPILE_ERROR
//                || $error['type'] == E_USER_ERROR) {
//
//
//                AxisErrorHandler(
//                    $error['type'],
//                    $error['message'],
//                    $error['file'],
//                    $error['line']);
//            }
//        }
//
//        register_shutdown_function('AxisFatalErrorHandler');
    }

    protected function _initLoader()
    {
        $this->bootstrap('Enviroment');
        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace(
            $this->getApplication()->getNamespaces()
        );
        return $autoloader;
    }

    protected function _initApplication()
    {
        $this->bootstrap('Loader');
        Zend_Registry::set('app', $this->getApplication());
        return Axis::app();
    }

    protected function _initConfig()
    {
        $this->bootstrap('Application');
        require_once AXIS_ROOT . '/app/etc/config.php';
        Zend_Registry::set('config', new Axis_Config($config, true));
        return Axis::config();
    }

    protected function _initSession()
    {
        $cacheDir = AXIS_ROOT . '/var/sessions';
        if (!is_readable($cacheDir)) {
            mkdir($cacheDir, 0777);
        } elseif (!is_writable($cacheDir)) {
            chmod($cacheDir, 0777);
        }

        Zend_Session::setOptions(array(
            'cookie_lifetime' => 864000, // 10 days
            'name'            => 'axisid',
            'strict'          => 'off',
            'save_path'       => $cacheDir,
            'cookie_httponly' => true
        ));

        try {
            Zend_Session::start();
        } catch (Zend_Session_Exception $e) {
            Zend_Session::destroy(); // ZF doesn't allow to start session after destroying
            if (!headers_sent()) {
                $host  = $_SERVER['HTTP_HOST'];
                $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                header("Location: http://$host$uri/");
            }
            exit();
        }
        return Axis::session();
    }

    protected function _initSessionValidators()
    {
        $this->bootstrap('DbAdapter');
        $sessionConfig = Axis::config('core/session');
        if (!$sessionConfig instanceof Axis_Config) {
            return;
        }
        if ($sessionConfig->remoteAddressValidation) {
            Zend_Session::registerValidator(new Axis_Session_Validator_RemoteAddress());
        }
        if ($sessionConfig->httpUserAgentValidation) {
            Zend_Session::registerValidator(new Zend_Session_Validator_HttpUserAgent());
        }
    }

    protected function _initDbAdapter()
    {
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        $db = Zend_Db::factory('Pdo_Mysql', array(
            'host'           => $config->db->host,
            'username'       => $config->db->username,
            'password'       => $config->db->password,
            'dbname'         => $config->db->dbname,
            'charset'        => 'UTF8'
//            'driver_options' => array(
//                //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
//                1002 => 'SET NAMES UTF8'
//            )
        ));

        //Set default adapter for childrens Zend_Db_Table_Abstract
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        //Axis_Config::setDefaultDbAdapter($db);

        Zend_Registry::set('db', $db);
        return Axis::db();
    }

    protected function _initCache()
    {
        $this->bootstrap('DbAdapter');
        //create default cache
        $cache = Axis_Core_Model_Cache::getCache();
        //create database metacache
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

//        /**
//         * Zend_View acceleration
//         * http://www.web-blog.org.ua/articles/uskoryaem-zend-view
//         */
//        $cacheDir = Axis::config()->system->path . '/var/PluginLoader/';
//        if (!is_readable($cacheDir)) {
//            mkdir($cacheDir, 0777);
//        } elseif(!is_writable($cacheDir)) {
//            chmod($cacheDir, 0777);
//        }
//        require_once 'Zend/Loader/PluginLoader.php';
//        $classFileIncCache = $cacheDir . 'cache.php';
//        if (file_exists($classFileIncCache))  {
//            include_once $classFileIncCache;
//        } else {
//            file_put_contents($classFileIncCache, "<?php\n");
//        }
//        Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);

        return Axis::cache();
    }

    protected function _initFrontController()
    {
        $this->bootstrap('Cache');
        $front = Zend_Controller_Front::getInstance();

//        $front->setDispatcher(new Axis_Controller_Dispatcher_Standard());
//        $front->throwExceptions(false);
        $front->setDefaultModule('Axis_Core');
        $front->setControllerDirectory(Axis::app()->getControllers());
//        $front->setParam('noViewRenderer', true);
        $front->registerPlugin(
            new Axis_Controller_Plugin_ErrorHandler_Override(), 10
        );

        $authActionHelper = new Axis_Controller_Action_Helper_Auth();
        Zend_Controller_Action_HelperBroker::addHelper($authActionHelper);

        return $front; // this is *VERY* important
    }

    protected function _initRouter()
    {
        $this->bootstrap('FrontController');
        $router = new Axis_Controller_Router_Rewrite();

        // pre router config
        $defaultLocale = Axis_Locale::getDefaultLocale();
        $locales = Axis_Locale::getLocaleList(true);

        Axis_Controller_Router_Route_Front::setDefaultLocale($defaultLocale);
        Axis_Controller_Router_Route_Front::setLocales($locales);

        // include routes files
        $routeFiles = Axis::app()->getRoutes();
        foreach ($routeFiles as $routeFile) {
            if (!is_readable($routeFile)) {
                continue;
            }
            include_once($routeFile);
        }

        $router->removeDefaultRoutes();

        if (!($router instanceof Axis_Controller_Router_Rewrite)) {
            throw new Axis_Exception('Incorrect routes');
        }
        $front = $this->getResource('FrontController');
        $front->setRouter($router);

        $sslRedirectorActionHelper = new Axis_Controller_Action_Helper_SecureRedirector();
        Zend_Controller_Action_HelperBroker::addHelper($sslRedirectorActionHelper);

        return $router;
    }

    protected function _initLocale()
    {
        $this->bootstrap('FrontController');

        //set default timezone affect on date() and Axis_Date
        Axis_Locale::setTimezone(Axis_Locale::getDefaultTimezone());

        $front = $this->getResource('FrontController');
        $front->registerPlugin(new Axis_Controller_Plugin_Locale(), 20);
    }

    protected function _initView()
    {
        $this->bootstrap('FrontController');
        $view = new Zend_View();
        $viewRenderer = new Axis_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setNeverRender()
            ->setNoController()
            ->setView($view)
            ->autoAddBasePaths(false);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        $jsonActionHelper = new Axis_Controller_Action_Helper_Json();
        Zend_Controller_Action_HelperBroker::addHelper($jsonActionHelper);

        $breadcrumbsActionHelper = new Axis_Controller_Action_Helper_Breadcrumbs();
        Zend_Controller_Action_HelperBroker::addHelper($breadcrumbsActionHelper);

        return $view;
    }

    protected function _initLayout()
    {
        $this->bootstrap('View');
        $layout = Axis_Layout::startMvc();

        $view = $this->getResource('View');
        $layout->setView($view);

        $front = $this->getResource('FrontController');
        $front->unregisterPlugin('Zend_Layout_Controller_Plugin_Layout');
        $front->registerPlugin(new Axis_Controller_Plugin_Layout($layout), 99);

        $layoutActionHelper = new Axis_Controller_Action_Helper_Layout($layout);
        Zend_Controller_Action_HelperBroker::addHelper($layoutActionHelper);

        return $layout;
    }

    protected function _initDebug()
    {
        $this->bootstrap('FrontController');
        if (APPLICATION_ENV !== 'development') {
            return;
        }

        //set query profiler
        $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        $profiler->setEnabled(true);
        $db = $this->getResource('DbAdapter');
        $db->setProfiler($profiler);

        return;

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        $options = array(
            'plugins' => array(
                'Variables',
                'File' => array('base_path' => realpath('..')),
                'Memory',
                'Time',
                'Registry',
                'Exception',
                'Html',
            )
        );

        // Настройка плагина для адаптера базы данных
        if ($this->hasResource('DbAdapter')) {
            $this->bootstrap('DbAdapter');
//            $db = $this->getResource('DbAdapter');//->getDbAdapter();
            $options['plugins']['Database']['adapter'] = $db;


            //set query profiler
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled(true);
            $db->setProfiler($profiler);
        }

        // Настройка плагина для кеша
        if ($this->hasResource('Cache')) {
            $this->bootstrap('Cache');
            $cache = $this->getResource('Cache');//->getDbAdapter();
            $options['plugins']['Cache']['backend'] = $cache->getBackend();
        }

        $debug = new ZFDebug_Controller_Plugin_Debug($options);

        $this->bootstrap('FrontController');
        $frontController = $this->getResource('FrontController');
        $frontController->registerPlugin($debug);
    }

    /**
     * Apply all upgrades to installed modules if config enabled
     *
     * @see AXIS_ROOT/app/etc/config.php
     */
    protected function _initModules()
    {
        $this->bootstrap('DbAdapter');

        if (!Axis::config('system/applyUpgrades')) {
            return;
        }
        $mModule = Axis::model('core/module');
        foreach ($mModule->fetchAll() as $module) {
            $module->upgradeAll();
        }
    }
}