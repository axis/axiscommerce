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
 * @package     Axis_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Controller_Action extends Zend_Controller_Action
{
    protected $_nsMain;

    /**
     *
     * @param  string $app
     * @return string
     */
    private function _getScriptsPath($app)
    {
        if ('front' === $app) {
            list($namespace, $module) = explode(
                '_', strtolower($this->getRequest()->getModuleName()), 2
            );
        } else {
            $controller = $this->getRequest()->getControllerName();
            if (false === strpos($controller, '_')) {
                $module = 'core';
            } else {
                $controllerArray = explode('_', $controller);
                $module = current($controllerArray);
            }
        }
        return $module;
    }

    /**
     * Initialize View object
     *
     * @param string $area
     * @param string $template
     * @return Zend_View_Interface
     * @see Zend_Controller_Action initView()
     */
    public function initView($area = null, $template = null)
    {
        //$view = parent::initView();
        require_once 'Zend/View/Interface.php';
        if (!$this->getInvokeArg('noViewRenderer')
            && $this->_helper->hasHelper('viewRenderer')) {

            $view = $this->view;
        } elseif (isset($this->view)
            && ($this->view instanceof Zend_View_Interface)) {

            $view = $this->view;
        } else {
            require_once 'Zend/View.php';
            $view = new Zend_View();
        }
        if ($view->templateName) {
            return $view;
        }
        
        if (null === $area) {
            $area = Zend_Registry::get('area');
        }
        if (null === $template) {
            $template = Axis_Layout::getTemplateName($area);
        }

        $request = $this->getRequest();
        $systemPath = Axis::config('system/path');

        $view->templateName = $template;
        $view->area         = $area;
        $module = $request->getModuleName();
        list($namespace, $module) = explode('_', $module, 2);
        $view->namespace  = $namespace;
        $view->moduleName = $module;

        $view->path = $systemPath;
        $view->skinPath = $systemPath . '/skin/' . $area . '/' . $template;

        $currentUrl = $request->getScheme() . '://'
             . $request->getHttpHost()
             . $request->getRequestUri();

        $site = Axis::getSite();

        $view->baseUrl      = $site ?
            $site->base : Zend_Controller_Front::getInstance()->getBaseUrl();
        $view->secureUrl    = $site ? $site->secure : $view->baseUrl;
        $view->resourceUrl  = (0 === strpos($currentUrl, $view->secureUrl)) ?
            $view->secureUrl : $view->baseUrl;
        $view->catalogUrl   = Axis::config('catalog/main/route');

        //@TODO every template shoud have own defaults
        //$view->defaultTemplate = 'default';

        //Initialize Zend_View stack
        $module = $this->_getScriptsPath($area);

        $view->addFilterPath($systemPath . '/library/Axis/View/Filter', 'Axis_View_Filter');
        $view->addHelperPath($systemPath . '/library/Axis/View/Helper', 'Axis_View_Helper');
        $view->setScriptPath(array());

        $fallbackList = array_unique(array(
            $template,
            /* @TODO user defined default: $view->defaultTemplate */
            'fallback',
            'default'
        ));
        foreach (array_reverse($fallbackList) as $fallback) {
            $templatePath = $systemPath . '/app/design/' . $area . '/' . $fallback;
            if (is_readable($templatePath . '/helpers')) {
                $view->addHelperPath($templatePath . '/helpers', 'Axis_View_Helper');
            }
            if (is_readable($templatePath . '/templates')) {
                $view->addScriptPath($templatePath . '/templates');
                $view->addScriptPath($templatePath . '/templates/' . $module);
            }
            if (is_readable($templatePath . '/layouts')) {
                $view->addScriptPath($templatePath . '/layouts');
            }
        }

        // setting default meta tags
        $view->meta()->setDefaults();

        $view->doctype('XHTML1_STRICT');

        $view->setEncoding('UTF-8');
        //$view = Axis::app()->getBootstrap()->getResource('View');
        $this->view = $view;

        //for compatibility
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view);

        return $view;
    }

    /**
     *
     * @param Axis_View|null $view
     * @param string $area
     * @param string $template
     * @return Axis_Layout
     */
    public function initLayout($view = null, $area = null, $template = null)
    {
        if (null === $view) {
            $view = $this->view;
        }
        if (null === $area) {
            $area = Zend_Registry::get('area');
        }
        if (null === $template) {
            $template = Axis_Layout::getTemplateName($area);
        }

        $this->layout = Axis_Layout::getMvcInstance();

        $this->layout->setView($view)->setOptions(array('layoutPath' =>
            Axis::config()->system->path .
            '/app/design/' . $area . '/' . $template . '/layouts'
        ));

        return $this->layout;
    }

    /**
     *  Main init
     */
    public function init()
    {
        parent::init();

        $this->db = Axis::db();

        $module = $this->getRequest()->getParam('module');
        $area = ($module === 'Axis_Admin') ? 'admin' : 'front';
        Zend_Registry::set('area', $area);
        $template = Axis_Layout::getTemplateName($area);
        $this->initView($area, $template);
        $this->initLayout($this->view, $area, $template);
        
        if ('front' === $area
            && $this->_hasParam('locale')
            && Axis_Controller_Router_Route::hasLocaleInUrl()) {

            $locale = $this->_getParam('locale');
        } elseif (isset(Axis::session()->locale)) {
            $locale = Axis::session()->locale;
        } else {
            $locale = Axis_Locale::getDefaultLocale();
        }
        Axis_Locale::setLocale($locale);
        
        Axis::translate();

        //$this->_helper->removeHelper('json');
        $this->_helper->addHelper(new Axis_Controller_Action_Helper_Json());
    }

    /**
     * Write a snapshot to session
     *
     * @param string $snapshot
     * @return void
     */
    protected function _setSnapshot($snapshot)
    {
        Axis::session()->snapshot = $snapshot;
    }

    /**
     * Retrieve snapshot from session
     *
     * @return string
     */
    protected function _getSnapshot()
    {
        $snapshot = Axis::session()->snapshot;
        unset(Axis::session()->snapshot);
        return $snapshot;
    }

    /**
     * @return bool
     */
    protected function _hasSnapshot()
    {
        return isset(Axis::session()->snapshot)
            && !empty(Axis::session()->snapshot);
    }
}