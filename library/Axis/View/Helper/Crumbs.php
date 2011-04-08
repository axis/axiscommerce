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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Crumbs
{
    protected $_crumbs = array();
    private $_status = true;

    /**
     * Add crumb
     * @return void
     * @param string $title
     * @param string $uri
     */
    public function add($title, $uri = null)
    {
        if (null === $uri) {
            $request = Zend_Controller_Front::getInstance()->getRequest();

            $uri = $request->getScheme() . '://'
                 . $request->getHttpHost()
                 . $request->getRequestUri();

        } elseif (substr($uri, 0 , 4) !== 'http') {
            $uri = $this->view->href($uri);
        }

        if (empty($this->_crumbs[$uri])) {
            $this->_crumbs[$uri] = array('title' => $title, 'url' => $uri);
        }
//        Axis_FirePhp::callstack();
        $container = $this->view->breadcrumbs;
        
        $iterator = new RecursiveIteratorIterator($container,
                RecursiveIteratorIterator::SELF_FIRST);
        
        foreach ($iterator as $_page) {
            if ($_page->get('uri') == $uri) {
                return;
            }
            $container = $_page;
        }
        
        $page = new Zend_Navigation_Page_Uri(array(
            'title'  => $title, 
            'uri'    => $uri, 
            'active' => true 
        ));
        $container->addPage($page);
    }

    /**
     *  Set crumbs
     * @return void
     * @param array of array $crumbs[optional]
     */
    public function set(array $crumbs = array())
    {
        $this->_crumbs = array();
        foreach($crumbs as $crumb) {
            $this->_crumbs[md5($crumb['url'] . $crumb['title'])] = $crumb;
        }
    }

    public function __toString()
    {
        if (!$this->_status) {
            return '';
        }

        $crumbs = $this->_crumbs;
        $last = array_pop($crumbs);
        $crumbs[] = array('title' => $last['title']);
        $content = '';
        $content .= '<div class="breadcrumbs-container"><ul class="breadcrumbs">';
        foreach ($crumbs as $crumb) {
            if (!empty($crumb['url'])) {
                $content .= '<li><a href="' . $crumb['url'].'">'
                          . $this->view->escape($crumb['title']) . '</a></li>';
            } else {
                $content .= '<li><span>' . $this->view->escape($crumb['title'])
                    . '</span></li>';
            }
        }
        $content .= '</ul></div>';
        return $content;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function crumbs()
    {
        return $this;
    }

    public function disable()
    {
        $this->_status = false;
    }

    public function enable()
    {
        $this->_status = true;
    }
}