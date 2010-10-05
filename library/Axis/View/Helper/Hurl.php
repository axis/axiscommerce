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
 * @package     Axis_Catalog
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Hurl
{
    static $keywords;
    private $_baseUrl;
    
    public function __construct()
    {
        $this->_hurl = Axis_HumanUri::getInstance();
        $this->_enabledSsl = Axis::config('core/frontend/ssl');
    }
    
    public function hurl(array $options = array(), $ssl = false, $reset = false)
    {
        return (($ssl && $this->_enabledSsl) ? $this->view->secureUrl : $this->view->baseUrl)
            . Axis_Locale::getLanguageUrl() . '/'
            . Axis::config('catalog/main/route')
            . $this->_hurl->url($options, $reset);
    }
    
    public function setView($view)
    {
        $this->view = $view;
    }
}