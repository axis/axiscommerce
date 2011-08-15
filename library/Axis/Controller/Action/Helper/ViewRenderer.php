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
 * @subpackage  Axis_Controller_Action_Helper
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer
{
    protected $_autoAddBasePaths = true;

    public function autoAddBasePaths($auto = true)
    {
        $this->_autoAddBasePaths = (bool) $auto;
        return $this;
    }

    /**
     *
     * @param string $path
     * @param string $prefix
     * @return Axis_Controller_Action_Helper_ViewRenderer 
     */
    protected function _autoAddBasePaths($path = null, $prefix = null)
    {
        if (false === $this->_autoAddBasePaths) {
            return $this;
        }
        // Get base view path
        if (empty($path)) {
            $path = $this->_getBasePath();
            if (empty($path)) {
                /**
                 * @see Zend_Controller_Action_Exception
                 */
                require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception('ViewRenderer initialization failed: retrieved view base path is empty');
            }
        }

        if (null === $prefix) {
            $prefix = $this->_generateDefaultPrefix();
        }

        // Determine if this path has already been registered
        $currentPaths = $this->view->getScriptPaths();
        $path         = str_replace(array('/', '\\'), '/', $path);
        $pathExists   = false;
        foreach ($currentPaths as $tmpPath) {
            $tmpPath = str_replace(array('/', '\\'), '/', $tmpPath);
            if (strstr($tmpPath, $path)) {
                $pathExists = true;
                break;
            }
        }
        if (!$pathExists) {
            $this->view->addBasePath($path, $prefix);
        }
        return $this;
    }

    /**
     * Initialize the view object
     *
     * $options may contain the following keys:
     * - neverRender - flag dis/enabling postDispatch() autorender (affects all subsequent calls)
     * - noController - flag indicating whether or not to look for view scripts in subdirectories named after the controller
     * - noRender - flag indicating whether or not to autorender postDispatch()
     * - responseSegment - which named response segment to render a view script to
     * - scriptAction - what action script to render
     * - viewBasePathSpec - specification to use for determining view base path
     * - viewScriptPathSpec - specification to use for determining view script paths
     * - viewScriptPathNoControllerSpec - specification to use for determining view script paths when noController flag is set
     * - viewSuffix - what view script filename suffix to use
     *
     * @param  string $path
     * @param  string $prefix
     * @param  array  $options
     * @throws Zend_Controller_Action_Exception
     * @return void
     */
    public function initView($path = null, $prefix = null, array $options = array())
    {
        if (null === $this->view) {
            $this->setView(new Zend_View());
        }

        // Reset some flags every time
        $options['noController'] = (isset($options['noController'])) ? $options['noController'] : false;
        $options['noRender']     = (isset($options['noRender'])) ? $options['noRender'] : false;
        $this->_scriptAction     = null;
        $this->_responseSegment  = null;

        // Set options first; may be used to determine other initializations
        $this->_setOptions($options);

        $this->_autoAddBasePaths($path, $prefix);

        // Register view with action controller (unless already registered)
        if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
            $this->_actionController->view       = $this->view;
            $this->_actionController->viewSuffix = $this->_viewSuffix;
        }
        
        $this->_initVars();
    }
    
    protected function _initVars() 
    {
//        return;
        $request = $this->getRequest();
        if (Axis_Area::isBackend()) {
            $templateId = Axis::config('design/main/adminTemplateId');
        } else {
            $templateId = Axis::config('design/main/frontTemplateId');
        }

        $theme = Axis::single('core/template')->getTemplateNameById($templateId);
        $systemPath = Axis::config('system/path');

        $this->view->templateName = $theme;
        $this->view->path         = $systemPath;
        $this->view->area = $area = Axis_Area::getArea();
        list($namespace, $module) = explode('_', $request->getModuleName(), 2);
        $this->view->namespace    = $namespace;
        $this->view->moduleName   = $module;
        

        $currentUrl = $request->getScheme() . '://'
             . $request->getHttpHost()
             . $request->getRequestUri();

        $site = Axis::getSite();

        $this->view->baseUrl      = $site ?
            $site->base : $this->getFrontController()->getBaseUrl();
        $this->view->secureUrl    = $site ? $site->secure : $this->view->baseUrl;
        $this->view->resourceUrl  = (0 === strpos($currentUrl, $this->view->secureUrl)) ?
            $this->view->secureUrl : $this->view->baseUrl;
        $this->view->catalogUrl   = Axis::config('catalog/main/route');

        //@TODO every template shoud have own defaults
        //$view->defaultTemplate = 'default';

        if (Axis_Area::isFrontend()) {
            $modulePath = strtolower($module);
        } else {
            $controller = $request->getControllerName();
            $modulePath = 'core';
            if (strpos($controller, '_')) {
                list($modulePath) = explode('_', $controller);
            }
        }

        $this->view->addFilterPath($systemPath . '/library/Axis/View/Filter', 'Axis_View_Filter');
        $this->view->addHelperPath(
            str_replace('_', '/', 'Zend_View_Helper_Navigation'),
            'Zend_View_Helper_Navigation'
        );

        $this->view->addHelperPath(
            $systemPath . '/library/Axis/View/Helper/Navigation',
            'Axis_View_Helper_Navigation'
        );
        $this->view->addHelperPath($systemPath . '/library/Axis/View/Helper', 'Axis_View_Helper');
        $this->view->setScriptPath(array());

        $themes = array_unique(array(
            $theme,
            /* @TODO user defined default: $view->defaultTemplate */
            'fallback',
            'default'
        ));
        foreach (array_reverse($themes) as $_theme) {
            $themePath = $systemPath . '/app/design/' . $area . '/' . $_theme;
            if (is_readable($themePath . '/helpers')) {
                $this->view->addHelperPath($themePath . '/helpers', 'Axis_View_Helper');
            }
            if (is_readable($themePath . '/templates')) {
                $this->view->addScriptPath($themePath . '/templates');
                $this->view->addScriptPath($themePath . '/templates/' . $modulePath);
            }
            if (is_readable($themePath . '/layouts')) {
                $this->view->addScriptPath($themePath . '/layouts');
            }
        }

        // setting default meta tags
        $this->view->meta()->setDefaults();

        $this->view->doctype('XHTML1_STRICT');

        $this->view->setEncoding('UTF-8');
        
        //@todo move this code (where?)
        $layout = Axis_Layout::getMvcInstance();

        $layout->setView($this->view)->setLayoutPath(
            $systemPath . '/app/design/' . $area . '/' . $theme . '/layouts'
        );
     }
}
