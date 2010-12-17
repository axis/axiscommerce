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
 * @subpackage  Axis_Install_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Install
 * @subpackage  Axis_Install_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Install_Model_Wizard
{
    const STEP_LICENSE       = 'license';
    const STEP_REQUIREMENTS  = 'requirements';
    const STEP_LOCALIZATION  = 'localization';
    const STEP_CONFIGURATION = 'configuration';
    const STEP_USER          = 'user';
    const STEP_MODULES       = 'modules';
    const STEP_FINISH        = 'finish';

    private static $_instance;

    /**
     *
     * @var const array
     */
    private $_steps = array(
        'license'       => 'License agreements',
        'requirements'  => 'Server requirements',
        'localization'  => 'Localization',
        'configuration' => 'Store configuration',
        'user'          => 'Setup admin account',
        'modules'       => 'Modules',
        'finish'        => 'All done'
    );

    /**
     *
     * @var Zend_Session_Namespace
     */
    private $_session;

    private function __construct()
    {
        $this->_session = Axis::session('install');
        if (!isset($this->_session->step)) {
            $this->_session->step = self::STEP_REQUIREMENTS;
        }
    }

    /**
     * Return instance of self
     *
     * @static
     * @return Axis_Install_Model_Wizard
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * @return const array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    public function getCurrent()
    {
        return $this->_session->step;
    }

    public function setStep($step)
    {
        $this->_session->step = $step;
    }

    /**
     * Return requirements
     *
     * @return array
     */
    public function checkRequirements()
    {
        $requirements = array(
            'Server Capabilities' => array(
                'php_version' => array(
                    'title'   => 'PHP Version',
                    'expected' => '>= 5.2',
                    'value'   => phpversion(),
                    'success' => phpversion() >= 5.2 ? true : false
                )
            ),
            'PHP Settings' => array(
                'magic_quotes' => array(
                    'title' => 'Magic quotes',
                    'expected' => 'Off'
                ),
                'file_uploads' => array(
                    'title' => 'File uploads',
                    'expected' => 'On'
                ),
                'session.auto_start' => array(
                    'title' => 'Session autostart',
                    'expected' => 'Off'
                ),
                'session.use_trans_sid' => array(
                    'title' => 'Session use trans SID',
                    'expected' => 'Off'
                )
            ),
            'PHP Extensions' => array(
                'pdo_mysql' => array(
                    'title' => 'pdo_mysql',
                    'expected' => 'Loaded'
                ),
                'gd' => array(
                    'title' => 'gd',
                    'expected' => 'Loaded'
                ),
                'curl' => array(
                    'title' => 'curl',
                    'expected' => 'Loaded'
                ),
                'mcrypt' => array(
                    'title' => 'MCrypt',
                    'expected' => 'Loaded'
                )
            ),
            'File Permissions' => array(
                '../var' => array(
                    'title' => '/var',
                    'expected' => 'Writable'
                ),
                '../app/etc' => array(
                    'title' => '/app/etc',
                    'expected' => 'Writable'
                ),
                '../media' => array(
                    'title' => '/media',
                    'expected' => 'Writable'
                )
            )
        );

        foreach ($requirements['PHP Settings'] as $key => &$values) {
            if ('magic_quotes' == $key) {
                $values['value'] = get_magic_quotes_gpc() === 1 ? 'On' : 'Off';
            } else {
                $values['value'] = intval(ini_get($key)) === 1 ? 'On' : 'Off';
            }
            $values['success'] = $values['value'] === $values['expected'] ? true : false;
        }
        foreach ($requirements['PHP Extensions'] as $key => &$values) {
            $values['value'] = extension_loaded($key) ? 'Loaded' : 'Not Loaded';
            $values['success'] = $values['value'] === $values['expected'] ? true : false;
        }
        foreach ($requirements['File Permissions'] as $key => &$values) {
            $values['value'] = is_writable($key) ? 'Writable' : 'Not Writable';
            $values['success'] = $values['value'] === $values['expected'] ? true : false;
        }

        return $requirements;
    }

    /**
     * Checks, database connection
     *
     * @return Axis_Install_Model_Wizard Provides fluent interface
     * @throws Axis_Exception
     */
    public function checkConnection()
    {
        $conn = @mysql_connect(
            $this->_session->db_host,
            $this->_session->db_username,
            $this->_session->db_password
        );
        if (!$conn) {
            throw new Axis_Exception(Axis::translate()->__(
                "Can't connect to database. Check server name, username or user password"
            ));
        }

        if (!mysql_select_db($this->_session->db_dbname, $conn)) {
            throw new Axis_Exception(Axis::translate()->__(
                "Can't select this database, check database name"
            ));
        }
        return $this;
    }

    /**
     *
     * @param int $minlength [optional]
     * @param int $maxlength [optional]
     * @param bool $useupper [optional]
     * @param bool $usespecial [optional]
     * @param bool $usenumbers [optional]
     * @return string
     */
    private function _generateKey(
                                  $minlength = 32,
                                  $maxlength = 64,
                                  $useupper = true,
                                  $usespecial = true,
                                  $usenumbers = true)
    {

        $charset = "abcdefghijklmnopqrstuvwxyz";
        if ($useupper) { $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; }
        if ($usenumbers) { $charset .= "0123456789"; }
        if ($usespecial) { $charset .= "~@#$%^*()_+-={}|]["; }   // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
        if ($minlength > $maxlength) { $length = mt_rand($maxlength, $minlength); }
        else { $length = mt_rand($minlength, $maxlength);}
        $key = null;
        for ($i = 0; $i < $length; $i++)  {
            $key .= $charset[(mt_rand(0, (strlen($charset) - 1)))];
        }
        return $key;
    }

    /**
     * Write store config options to session
     *
     * @return Axis_Install_Model_Wizard Provides fluent interface
     */
    public function initStore()
    {
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->_session->store_path = str_replace('\\', '/', realpath('..'));
        if (empty($this->_session->store_baseUrl)) {
            $this->_session->store_baseUrl = 'http://' . $_SERVER['HTTP_HOST']
                . str_replace('/install', '', $baseUrl);
        }
        if (empty($this->_session->store_secureUrl)) {
            $this->_session->store_secureUrl = 'https://' . $_SERVER['HTTP_HOST']
                . str_replace('/install', '', $baseUrl);
        }
        if (empty($this->_session->store_adminUrl)) {
            $this->_session->store_adminUrl = 'admin';
        }
        if (empty($this->_session->store_cryptKey)) {
            $this->_session->store_cryptKey = md5($this->_generateKey());
        }
        return $this;
    }

    /**
     * Insert row to the core_site table
     *
     * @return Axis_Install_Model_Wizard Provides fluent interface
     */
    private function _installStore()
    {
        Axis::single('core/site')->insert(array(
            'base' => $this->_session->store_baseUrl,
            'secure' => $this->_session->store_secureUrl,
            'name' => 'Main Store' //@todo get from form
        ));

        Axis::single('core/config_value')->update(array(
            'value' => trim($this->_session->store_adminUrl, '/ ')
        ), "path = 'core/backend/route'");

        Axis::single('core/config_value')->update(array(
            'value' => $this->_session->use_ssl
        ), "path = 'core/backend/ssl'");

        Axis::single('core/config_value')->update(array(
            'value' => $this->_session->use_ssl
        ), "path = 'core/frontend/ssl'");

        Axis::single('core/config_value')->update(array(
            'value' => $this->_session->user_firstname . ' '
            . $this->_session->user_lastname
        ), "path = 'core/store/owner'");

        Axis::single('core/config_value')->update(array(
            'value' => $this->_session->user_email
        ), "path = 'mail/mailboxes/email1'");

        Axis::single('core/config_value')->update(array(
            'value' => 'email1'
        ), "path = 'core/company/administratorEmail'");

        return $this;
    }

    /**
     * Inserts selected timezone, locales and currencies
     *
     * @return Axis_Install_Model_Wizard Provides fluent interface
     */
    private function _installLocale()
    {
        /* setting default store values */
        Axis::single('core/config_value')->update(array(
            'value' => current($this->_session->locale['timezone'])
        ), "path = 'locale/main/timezone'");

        Axis::single('core/config_value')->update(array(
            'value' => current($this->_session->locale['locale'])
        ), "path = 'locale/main/locale'");

        Axis::single('core/config_value')->update(array(
            'value' => current($this->_session->locale['currency'])
        ), "path = 'locale/main/currency'");

        Axis::single('core/config_value')->update(array(
            'value' => current($this->_session->locale['currency'])
        ), "path = 'locale/main/baseCurrency'");

        /* setting languages and currencies available on frontend */
        foreach ($this->_session->locale['locale'] as $locale) {
            $code = current(explode('_', $locale));
            $language = Zend_Locale::getTranslation($code, 'language', $locale);
            if (!$language) {
                $language = Zend_Locale::getTranslation($code, 'language', 'en_US');
            }
            Axis::single('locale/language')->insert(array(
                'code' => $code,
                'language' => ucfirst($language),
                'locale' => $locale
            ));
        }

        reset($this->_session->locale['locale']);

        foreach ($this->_session->locale['currency'] as $currency) {
            $title = Zend_Locale::getTranslation(
                $currency, 'NameToCurrency', current($this->_session->locale['locale'])
            );
            if (!$title) {
                $title = Zend_Locale::getTranslation($currency, 'NameToCurrency', 'en_US');
            }
            Axis::single('locale/currency')->insert(array(
                'code' => $currency,
                'currency_precision' => 2,
                'display' => 2,
                'format' => current($this->_session->locale['locale']),
                'position' =>  8,
                'title' => $title ? ucfirst($title) : $currency,
                'rate' => 1
            ));
        }
        return $this;
    }

    /**
     * Cleares admin_user table and insert a new record into it
     *
     * @return Axis_Install_Model_Wizard Provides fluent interface
     */
    private function _addUser()
    {
        $date = date("Y-m-d H:i:s");

        Axis::single('admin/user')->delete('id = 1');
        Axis::single('admin/user')->insert(array(
            'id'            => 1,
            'role_id'       => 1,
            'firstname'     => $this->_session->user_firstname,
            'lastname'      => $this->_session->user_lastname,
            'email'         => $this->_session->user_email,
            'username'      => $this->_session->user_login,
            'password'      => md5($this->_session->user_password),
            'created'       => $date,
            'modified'      => $date,
            'lastlogin'     => $date,
            'lognum'        => 0,
            'reload_acl_flag' => 0,
            'is_active'     => 1
        ));
        return $this;
    }

    /**
     * Run modules installation
     *
     * @return Axis_Install_Model_Wizard Provides fluent interface
     */
    public function run()
    {
        @set_time_limit(300);

        $modelModule = Axis::single('core/module');
        if (!count(Axis::db()->fetchAll("SHOW TABLES LIKE '%core_module'"))) {
            $modelModule->getByCode('Axis_Core')->install();
            $this->_installStore();
            $modelModule->getByCode('Axis_Locale')->install();
            $this->_installLocale();
        }

        foreach ($this->_session->modules as $code) {
            if (!strstr($code, '_')) {
                continue;
            }
            $this->log("Module {$code}:");
            $moduleRow = $modelModule->getByCode($code);
            if ($moduleRow->isInstalled()) {
                $this->log("\tSkipped (already installed)");
                continue;
            }
            $this->log("\tBegin");
            $moduleRow->install();
            $this->log("\tEnd");
        }

        $this->_addUser();

        return $this;
    }

    public function applyTemplate()
    {
        Axis::single('core/template')->importTemplateFromXmlFile(
            AXIS_ROOT
             . '/app/code/Axis/Install/etc/'
             . 'default.xml'
        );
        return $this;
    }

    public function log($message)
    {
        try {
            $logger = new Zend_Log(new Zend_Log_Writer_Stream(
                AXIS_ROOT . '/var/logs/installation.log'
            ));
            $logger->log($message, Zend_Log::DEBUG);
        } catch (Exception $e) {
            error_log($e->getMessage());
            error_log($message);
        }
    }
}
