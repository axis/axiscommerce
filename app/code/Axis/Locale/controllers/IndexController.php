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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_IndexController extends Axis_Core_Controller_Front
{   
    public function changeAction()
    {
        $locale = $this->_getParam('lang');
        $redirectUrl = $lastUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ($locale) {
            $langUrl = Axis_Locale::getLanguageUrl();
            if (0 === strpos($redirectUrl, $this->view->secureUrl)) {
                $urlPrefix = $this->view->secureUrl . $langUrl;
            } else {
                $urlPrefix = $this->view->baseUrl . $langUrl;
            }
            $redirectUrl = substr($lastUrl, strpos($lastUrl, $urlPrefix) + strlen($urlPrefix));
            Axis_Locale::setLocale($locale);
        }
        $this->_redirect($redirectUrl);
    }
    
}