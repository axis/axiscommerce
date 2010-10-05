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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Image
{
    public function image($src, $attribute = '', $absolute = true)
    {
        $path = $this->view->resourceUrl;
        if (!$absolute) {
            $path = Zend_Controller_Front::getInstance()->getBaseUrl();    
        }

        if (!strstr($attribute, 'alt=')) {
            $attribute .= ' alt="' . str_replace(' ', '', strtolower($src)) . '"';
        }

        if (is_readable($this->view->path . '/skin/' . $this->view->area
                . '/' . $this->view->templateName . '/images/' . $src)) {
            return '<img src="' . $path . '/skin/' . $this->view->area .'/'
                . $this->view->templateName . '/images/' . $src . '" '
                . $attribute . ' />';
        } else {
            return '<img src="' . $path .
                '/skin/' . $this->view->area . '/default/images/' . $src . '" '
                . $attribute .' />';
        }
    }
    
    public function setView($view)
    {
        $this->view = $view;
    }
}
