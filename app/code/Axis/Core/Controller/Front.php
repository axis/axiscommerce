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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Controller_Front extends Axis_Controller_Action
{
    public function auth()
    {
        if (!Axis::getCustomerId()) {
            $this->_redirect('/account/auth');
        }
    }
    
    public function init()
    {
        parent::init();
        Axis::single('account/customer')->checkIdentity();
        $this->view->crumbs()->add(
            Axis::translate('core')->__(
                'Home'
            ),
            $this->view->href('/')
        );
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