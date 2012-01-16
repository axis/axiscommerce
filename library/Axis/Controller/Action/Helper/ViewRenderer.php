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
 * @copyright   Copyright 2008-2012 Axis
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
    /**
     * View script path specification string
     * @var string
     */
    protected $_viewScriptPathSpec = ':module/:controller/:action.:suffix';

    /**
     *
     * @var bool
     */
    protected $_autoAddBasePaths = true;

    /**
     *
     * @param bool $auto
     * @return Axis_Controller_Action_Helper_ViewRenderer
     */
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
        $this->_initScriptPaths();
        $this->_initDefaults();
    }

    protected function _initVars()
    {
        $view  = $this->view;
        $request = $this->getRequest();
        if (Axis_Area::isBackend()) {
            $templateId = Axis::config('design/main/adminTemplateId');
        } else {
            $templateId = Axis::config('design/main/frontTemplateId');
        }

        $view->templateName = Axis::single('core/template')->getTemplateNameById($templateId);
        $view->path         = Axis::config('system/path');
        $view->area         = Axis_Area::getArea();

        list($view->namespace, $view->moduleName) = explode('_', $request->getModuleName(), 2);


        $currentUrl = $request->getScheme() . '://'
             . $request->getHttpHost()
             . $request->getRequestUri();

        $site = Axis::getSite();

        $view->baseUrl      = $site ?
            $site->base : $this->getFrontController()->getBaseUrl();
        $view->secureUrl    = $site ? $site->secure : $view->baseUrl;
        $view->resourceUrl  = (0 === strpos($currentUrl, $view->secureUrl)) ?
            $view->secureUrl : $view->baseUrl;
        $view->catalogUrl   = Axis::config('catalog/main/route');
    }

    protected function _initScriptPaths()
    {
        //@TODO every template shoud have own defaults
        //$view->defaultTemplate = 'default';
        $view  = $this->view;
        $path  = $view->path;
        $area  = $view->area;
        $theme = $view->templateName;

        $view->addFilterPath(
                $path . '/library/Axis/View/Filter', 'Axis_View_Filter'
            )->addHelperPath(
                'Zend/View/Helper/Navigation', 'Zend_View_Helper_Navigation'
            )->addHelperPath(
                $path . '/library/Axis/View/Helper/Navigation', 'Axis_View_Helper_Navigation'
            )->addHelperPath(
                $path . '/library/Axis/View/Helper', 'Axis_View_Helper'
            )->setScriptPath(array());

        $themes = array_unique(array(
            $theme,
            /* @TODO user defined default: $view->defaultTemplate */
            'fallback',
            'default'
        ));
        foreach (array_reverse($themes) as $_theme) {
            $themePath = $path . '/app/design/' . $area . '/' . $_theme;
            if (is_readable($themePath . '/helpers')) {
                $view->addHelperPath($themePath . '/helpers', 'Axis_View_Helper');
            }
            if (is_readable($themePath . '/templates')) {
                $view->addScriptPath($themePath . '/templates');
            }
            if (is_readable($themePath . '/layouts')) {
                $view->addScriptPath($themePath . '/layouts');
            }
        }
        $this->getInflector()
            ->addFilterRule('module', new Zend_Filter_PregReplace('/^.+_(.+)$/', '$1'))
//            ->addFilterRule('module', new Zend_Filter_PregReplace('/^admin$/', ''))
            ->addFilterRule('controller', new Zend_Filter_PregReplace('/^admin\/(.+)$/', '$1'))
        ;
    }

    protected function _initDefaults()
    {
        $view = $this->view;
        // setting default meta tags
        $view->meta()->setDefaults();

        $view->doctype('XHTML1_STRICT');

        $view->setEncoding('UTF-8');
    }

    /**
     * Render a view script (optionally to a named response segment)
     * Overriden to add axis_controller_action_render_before event
     *
     * Sets the noRender flag to true when called.
     *
     * @param  string $script
     * @param  string $name
     * @return void
     */
    public function renderScript($script, $name = null)
    {
        if (null === $name) {
            $name = $this->getResponseSegment();
        }

        // before_render event
        $object = new Axis_Object(array(
            'request'   => $this->getRequest(),
            'response'  => $this->getResponse(),
            'script'    => $script,
            'name'      => $name
        ));
        Axis::dispatch('axis_controller_action_render_before', $object);
        $prefix = $this->getRequest()->getModuleName()
            . '_' . $this->getRequest()->getControllerName()
            . '_' . $this->getRequest()->getActionName();
        Axis::dispatch(strtolower($prefix) . '_render_before', $object);
        $script = $object->getScript();
        $name   = $object->getName();
        // end of before_render event

        $this->getResponse()->appendBody(
            $this->view->render($script),
            $name
        );

        $this->setNoRender();
    }
}
