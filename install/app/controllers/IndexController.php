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
 * @subpackage  Axis_Install_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Install
 * @subpackage  Axis_Install_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * Install model
     *
     * @var Axis_Install_Model_Wizard
     */
    private $_install;

    /**
     * Install session
     *
     * @var Zend_Session_Namespace
     */
    private $_session;

    public function init()
    {
        $this->initView();
        $layout = Zend_Layout::getMvcInstance();

        $this->_session = Axis::session('install');

        $this->view->baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->view->addHelperPath('app/views/helpers', 'Axis_View_Helper');
        $this->view->doctype('XHTML1_STRICT');

        $layout->setView($this->view)->setOptions(array(
            'layoutPath' => 'app/views/layouts',
            'layout' => 'layout'
        ));

        $this->_install = Axis_Install_Model_Wizard::getInstance();
    }

    public function preDispatch()
    {
        if ('development' === APPLICATION_ENV) { //@todo remove in release
            return;
        }
        if (Axis_Application::isInstalled()
            && !isset($this->_session->permit_installation)) {

            if (!headers_sent()) {
                $host  = $_SERVER['HTTP_HOST'];
                $uri   = rtrim(
                    dirname(str_replace('/install', '', $_SERVER['PHP_SELF'])),
                    '/\\'
                );
                header("Location: http://$host$uri/");
            }
            exit();
        }
    }

    public function indexAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_LICENSE);
        $this->view->pageTitle = 'License';
        $this->view->license = @file_get_contents('../license.txt');

        if (function_exists('apache_get_modules')) {
            if (!in_array('mod_rewrite', apache_get_modules())) {
                Axis::message()->addError(Axis::translate('install')->__(
                    'Your server must have mod_rewrite module loaded to go futher'
                ));
            }
        }
        $this->render('step-license');
    }

    public function stepRequirementsAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_REQUIREMENTS);
        $this->view->pageTitle = 'Requirements';
        $this->view->requirements = $this->_install->checkRequirements();
        $this->view->isValid = true;
        foreach ($this->view->requirements as $group) {
            foreach ($group as $item) {
                if (!$item['success']) {
                    Axis::message()->addError(Axis::translate('install')->__(
                        "Server configuration doesn't meet the Axis needs"
                    ));
                    $this->view->isValid = false;
                    break 2;
                }
            }
        }
        // if (!$this->view->isValid) {
            $this->render('step-requirements');
        // } else {
        //     $this->_redirect('index/step-localization');
        // }
    }

    public function checkRequirementsAction()
    {
        foreach ($this->_install->checkRequirements() as $item) {
            $item = current($item);
            if (!$item['success']) {
                $this->_redirect('index/step-requirements');
            }
        }
        $this->_redirect('index/step-localization');
    }

    public function stepLocalizationAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_LOCALIZATION);
        $this->view->pageTitle = 'Localization';
        $this->render('step-localization');
    }

    public function saveLocalizationAction()
    {
        $default_locale = $this->_getParam('default');
        $additional_locales = $this->_getParam('locale');
        $additional_currencies = $this->_getParam('currency');

        $this->_session->locale = array();
        $this->_session->locale['locale'] = array();
        $this->_session->locale['timezone'] = array();
        $this->_session->locale['currency'] = array();

        $this->_session->locale['locale'][$default_locale['locale']] =
            $default_locale['locale'];
        $this->_session->locale['timezone'][$default_locale['timezone']] =
            $default_locale['timezone'];
        $this->_session->locale['currency'][$default_locale['currency']] =
            $default_locale['currency'];

        if (is_array($additional_locales)) {
            foreach ($additional_locales as $locale) {
                $this->_session->locale['locale'][$locale] = $locale;
            }
        }
        if (is_array($additional_currencies)) {
            foreach ($additional_currencies as $currency) {
                $this->_session->locale['currency'][$currency] = $currency;
            }
        }
        $this->_redirect('index/step-configuration');
    }

    public function stepConfigurationAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_CONFIGURATION);
        $this->_install->initStore();
        $this->view->pageTitle = 'Configuration';

        $this->view->db = array(
            'host'     => $this->_session->db_host,
            'username' => $this->_session->db_username,
            'dbname'   => $this->_session->db_dbname,
            'prefix'   => $this->_session->db_prefix
        );

        $this->view->store = array(
            'base_url'   => $this->_session->store_baseUrl,
            'use_ssl'    => $this->_session->use_ssl,
            'secure_url' => $this->_session->store_secureUrl,
            'admin_url'  => $this->_session->store_adminUrl,
            'crypt_key'  => $this->_session->store_cryptKey
        );

        $this->render('step-configuration');
    }

    public function saveConfigurationAction()
    {
        $params = $this->_getAllParams();
        if (empty($params['host'])
            || empty($params['username'])
            || empty($params['database'])
            || empty($params['base_url'])
            || empty($params['admin_url'])) {

            Axis::message()->addError(Axis::translate('install')->__(
                'Fill all required fields please'
            ));
            $this->_redirect('index/step-configuration');
        }

        $this->_session->db_host     = $params['host'];
        $this->_session->db_username = $params['username'];
        $this->_session->db_password = $params['password'];
        $this->_session->db_dbname   = $params['database'];
        $this->_session->db_prefix   = $params['prefix'];

        $this->_session->use_ssl = (bool)$params['enable_ssl'];
        $this->_session->store_baseUrl   = $params['base_url'];
        $this->_session->store_secureUrl  = $this->_session->use_ssl ?
            $params['secure_url'] : $params['base_url'];
        $this->_session->store_adminUrl = $params['admin_url'];
        $this->_session->store_cryptKey = $params['crypt_key'];

        try {
            $this->_install->checkConnection();
        } catch (Exception $e) {
            $this->_session->store_secureUrl = $params['secure_url'];
            Axis::message()->addError($e->getMessage());
            $this->_redirect('index/step-configuration');
        }
        // this variable clears in step finish action, and checks in preDispatch
        $this->_session->permit_installation = true;
        $this->_saveConfig();
        $this->_redirect('index/step-user');
    }

    public function stepUserAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_USER);
        $this->view->pageTitle = 'Admin User Settings';
        $this->view->firstname = $this->_session->user_firstname;
        $this->view->lastname = $this->_session->user_lastname;
        $this->view->login = $this->_session->user_login;
        $this->view->email = $this->_session->user_email;
        $this->render('step-user');
    }

    public function saveUserAction()
    {
        $params = $this->_getAllParams();
        if (empty($params['login'])  ||
            empty($params['password'])  ||
            empty($params['email'])     ||
            empty($params['name']) ||
            empty($params['surname']))
        {
            Axis::message()->addError(Axis::translate('install')->__(
                'Fill all required fields please'
            ));
            $this->_redirect('index/step-user');
        }

        $this->_session->user_firstname = $params['name'];
        $this->_session->user_lastname  = $params['surname'];
        $this->_session->user_login     = $params['login'];
        $this->_session->user_email     = $params['email'];
        $this->_session->user_password  = $params['password'];
        $this->_redirect('index/step-modules');
    }

    public function stepModulesAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_MODULES);
        $this->view->pageTitle = 'Installation Mode';
        $this->view->modules = Axis_Install_Model_Module::getModules();
        $this->render('step-modules');
    }

    public function saveModulesAction()
    {
        $this->_session->modules = array_keys(
            array_filter($this->_getParam('modules'))
        );
        $this->_install();
    }

    public function stepFinishAction()
    {
        $this->_install->setStep(Axis_Install_Model_Wizard::STEP_FINISH);
        $this->view->pageTitle = 'Axis was successfully installed';
        $this->view->user_login = $this->_session->user_login;
        $this->view->user_password = $this->_session->user_password;
        $this->view->crypt_key = $this->_session->store_cryptKey;
        $this->view->permitInstallation = $this->_session->permit_installation;
        $this->view->frontend = $this->_session->store_baseUrl;
        $this->view->backend = $this->view->frontend . '/' . $this->_session->store_adminUrl;
        $locale = $this->_session->current_locale;
        $this->_session->unsetAll();
        $this->_session->current_locale = $locale;
        $this->render('step-finish');
    }

    /**
     * Change the locale
     */
    public function changeLocaleAction()
    {
        $locale = $this->_getParam('new_locale');

        if ($locale) {
            Axis_Locale::setLocale($locale);
        }

        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }

    private function _install()
    {
        try {
            $this->_install->run()->applyTemplate();
        } catch (Exception $e) {
            Axis::message()->addError($e->getMessage());
            Axis::message()->addError($e->getTraceAsString());
            $this->_install->log($e->getMessage());
            $this->_install->log($e->getTraceAsString());
            $this->_redirect('index/step-modules');
        }
        $this->_redirect('index/step-finish');
    }

    private function _saveConfig()
    {
        if (!file_exists($this->_getConfigPath())) {
            @touch($this->_getConfigPath());
        }
        if (is_writable($this->_getConfigPath())) {
            $content = $this->_getConfigContent();
            file_put_contents($this->_getConfigPath(), $content);
        } else {
            throw new Zend_Exception(
                $this->view->t("Config file is not writable at %s", $this->_getConfigPath())
            );
        }
    }

    private function _getConfigContent()
    {
        $config = array();
        foreach ($this->_session as $key => $value) {
            $config[$key] = $value;
        }
        if (empty($config['store_cryptKey'])) {
            $config['store_cryptKey'] = md5($this->_install->generateKey());
        }

        $this->view->config = $config;

        return $this->view->render('index/config.phtml');
    }

    private function _getConfigPath()
    {
        return $this->_session->store_path . '/app/etc/config.php';
    }

    public function dropAction()
    {
        Axis::single('core/cache')->clean();

        /**
         * @var Axis_Install_Model_Installer
         */
        $installer = Axis::single('install/installer');

        $installer->run("
            SET SQL_MODE='';
            SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
            SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
        ");

        $tables = Axis::db()->fetchAll('show tables');
        foreach ($tables as $table) {
            $tableName = current($table);
            $installer->run("DROP TABLE `{$tableName}`;");
        }

        Axis::single('install/installer')->run("
            SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'');
            SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS,0);
        ");

        unlink(AXIS_ROOT . '/app/etc/config.php');

        if (!headers_sent()) {
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(
                dirname(str_replace('/install', '', $_SERVER['PHP_SELF'])),
                '/\\'
            );
            header("Location: http://$host$uri/");
        }
        exit();
    }
}
