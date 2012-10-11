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
 * @subpackage  Axis_Core_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Controller_Front extends Axis_Controller_Action
{
    public function init()
    {
        parent::init();
        Axis::single('account/customer')->checkIdentity();
        $this->_helper->breadcrumbs(array(
            'label' => Axis::translate('core')->__('Home'),
            'route' => 'core'
        ));
        // fix to remove duplicate favicon, canonical when forwarding request
        // this is not an option, because we should allow to add resources from the bootstrap in future
        // $this->view->headLink()->getContainer()->exchangeArray(array());
    }

    /**
     *
     * @param string $title
     * @param string $metaTitle
     * @param string $labelBreadcrumb
     */
    public function setTitle($title, $metaTitle = null, $labelBreadcrumb = null)
    {
        $this->view->pageTitle = $title;

        if (null === $metaTitle) {
            $metaTitle = $title;
        }
        if (!empty($metaTitle)) {
            $this->view->meta()->setTitle($metaTitle);
        }
        if (null === $labelBreadcrumb) {
            $labelBreadcrumb = $title;
        }
        if (!empty($labelBreadcrumb)) {
            $request = $this->getRequest();
            $this->_helper->breadcrumbs(array(
                'label'      => $labelBreadcrumb,
                'module'     => $request->getModuleName(),
                'controller' => $request->getControllerName(),
                'action'     => $request->getActionName(),
                'params'     => $request->getParams()
            ));
        }
    }

    public function setCanonicalUrl($url)
    {
        $this->view->canonicalUrl = $url;
    }

    /**
     * Redirect to another URL
     *
     * @param string $url [optional]
     * @param bool $addLanguage [optional]
     * @param array $options Options to be used when redirecting
     * @return void
     */
    protected function _redirect(
        $url, array $options = array(), $addLanguage = true)
    {
        if (0 === strpos($url, 'http://')
            || 0 === strpos($url, 'https://')
            || !$addLanguage) {

            parent::_redirect($url, $options);
            return;
        }

        if ($url && $url !== '/') {
            $url = '/' . trim($url, '/');
        } else {
            $url = '';
        }
        parent::_redirect(Axis_Locale::getLanguageUrl() . $url, $options);
    }

    /**
     * Post-dispatch routines
     *
     * Called after action method execution. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to process an additional action.
     *
     * Common usages for postDispatch() include rendering content in a sitewide
     * template, link url correction, setting headers, etc.
     *
     * @return void
     */
    public function postDispatch()
    {
        $observer = new Axis_Object();
        $observer->controller = $this;
        Axis::dispatch('controller_action_postdispatch', $observer);

        if (null === $this->view->canonicalUrl) {
            $frontController = Zend_Controller_Front::getInstance();
            $router          = $frontController->getRouter();
            $params          = $frontController->getRequest()->getParams();
            $route           = $router->getCurrentRoute();
            $defaults        = $route->getDefaults();
            $canonicalParams = array();

            foreach ($route->getVariables() as $variable) {
                if (empty($params[$variable])) {
                    continue;
                }

                if (!empty($defaults[$variable])
                    && $params[$variable] === $defaults[$variable]) {

                    continue;
                }

                $canonicalParams[$variable] = $params[$variable];
            }

            foreach ($route->getWildcardData() as $name => $value) {
                $canonicalParams[$name] = $value;
            }

            $this->view->canonicalUrl = $this->view->url(
                $canonicalParams,
                $router->getCurrentRouteName(),
                true
            );
        }
        if (false !== $this->view->canonicalUrl) {
            $this->view->headLink(array(
                'rel'  => 'canonical',
                'href' => $this->view->canonicalUrl
            ), 'PREPEND');
        }
    }
}
