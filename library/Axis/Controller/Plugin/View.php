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
 * @subpackage  Plugin
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Plugin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Plugin_View extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_View
     */
    protected $_view;

    /**
     * Constructor
     *
     * @param  Zend_Layout $view
     * @return void
     */
    public function __construct(Zend_View $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }
    }

    /**
     * Retrieve layout object
     *
     * @return Zend_View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Set layout object
     *
     * @param  Zend_View $view
     * @return Axis_Controller_Plugin_View
     */
    public function setView(Zend_View $view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = $this->getView();

        if (Axis_Area::isBackend()) {
            $templateId = Axis::config('design/main/adminTemplateId');
        } else {
            $templateId = Axis::config('design/main/frontTemplateId');
        }

        $theme = Axis::single('core/template')->getTemplateNameById($templateId);
        $systemPath = Axis::config('system/path');

        $view->templateName = $theme;
        $view->area = $area = Axis_Area::getArea();
        list($namespace, $module) = explode('_', $request->getModuleName(), 2);
        $view->namespace    = $namespace;
        $view->moduleName   = $module;

        $view->path         = $systemPath;
        $view->skinPath     = $systemPath . '/skin/' . $area . '/' . $theme;

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

        if (Axis_Area::isFrontend()) {
            $modulePath = strtolower($module);
        } else {
            $controller = $request->getControllerName();
            $modulePath = 'core';
            if (strpos($controller, '_')) {
                list($modulePath) = explode('_', $controller);
            }
        }

        $view->addFilterPath($systemPath . '/library/Axis/View/Filter', 'Axis_View_Filter');
        $view->addHelperPath($systemPath . '/library/Axis/View/Helper', 'Axis_View_Helper');
        $view->setScriptPath(array());

        $themes = array_unique(array(
            $theme,
            /* @TODO user defined default: $view->defaultTemplate */
            'fallback',
            'default'
        ));
        foreach (array_reverse($themes) as $_theme) {
            $themePath = $systemPath . '/app/design/' . $area . '/' . $_theme;
            if (is_readable($themePath . '/helpers')) {
                $view->addHelperPath($themePath . '/helpers', 'Axis_View_Helper');
            }
            if (is_readable($themePath . '/templates')) {
                $view->addScriptPath($themePath . '/templates');
                $view->addScriptPath($themePath . '/templates/' . $modulePath);
            }
            if (is_readable($themePath . '/layouts')) {
                $view->addScriptPath($themePath . '/layouts');
            }
        }

        // setting default meta tags
        $view->meta()->setDefaults();

        $view->doctype('XHTML1_STRICT');

        $view->setEncoding('UTF-8');

        $layout = Axis_Layout::getMvcInstance();

        $layout->setView($view)->setLayoutPath(
            $systemPath . '/app/design/' . $area . '/' . $theme . '/layouts'
        );
    }
}
