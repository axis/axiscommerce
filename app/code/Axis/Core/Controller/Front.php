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
 * @copyright   Copyright 2008-2010 Axis
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
        $this->addBreadcrumb(array(
            'label' => Axis::translate('core')->__('Home'),
            'route' => 'core'
        ));
    }
    
    public function auth()
    {
        if (!Axis::getCustomerId()) {
            $this->_redirect('/account/auth');
        }
    }
    
    /**
     *
     * @param Zend_Navigation_Container $container
     * @return Axis_Core_Controller_Front 
     */
    public function setBreadcrumbs(Zend_Navigation_Container $container = null) 
    {
        if (null === $container) {
            $container = new Zend_Navigation();
        }
        Zend_Registry::set('axis/breadcrumbs', $container);
        return $this;
    }
    
    /**
     *
     * @return Zend_Navigation_Container 
     */
    public function getBreadcrumbs() 
    {
        if (!Zend_Registry::isRegistered('axis/breadcrumbs')) {
            $this->setBreadcrumbs();
        }
        return Zend_Registry::get('axis/breadcrumbs');
    }
    
    /**
     *
     * @param array $page
     * @return Axis_Core_Controller_Front 
     */
    public function addBreadcrumb(array $page) 
    {
        $container = $this->getBreadcrumbs();
        
        $iterator = new RecursiveIteratorIterator($container,
                RecursiveIteratorIterator::SELF_FIRST);
        
        foreach ($iterator as $_page) {
            $container = $_page;
        }
        
        $page['active'] = true;
        $container->addPage($page);
//        $this->setBreadcrumbs($page);
        return $this;
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
            $this->addBreadcrumb(array(
                'label'      => $labelBreadcrumb,
                'module'     => $request->getModuleName(),
                'controller' => $request->getControllerName(),
                'action'     => $request->getActionName(),
                'params'     => $request->getParams()
            ));
        }
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
        $httpReferer = $this->getRequest()->getServer('HTTP_REFERER');
        if (($httpReferer && $url == $httpReferer) || !$addLanguage) {

            parent::_redirect($url, $options);
            return;
        }
        parent::_redirect(
            rtrim(Axis_Locale::getLanguageUrl(), '/') . '/' . ltrim($url, '/'),
            $options
        );
    }
}