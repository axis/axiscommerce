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
 * @package     Axis_Admin
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Box
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Admin_Box_Abstract extends Axis_Core_Box_Abstract
{
    public function toHtml()
    {
        if (!$this->_isAllowed
            || false === $this->initData() 
            || !$this->hasContent()) {

            return '';
        }
        self::$view->box = $this;
        
        if (empty($this->_data['template'])) {
            $templateName = $this->_data['boxName'];
            $templateName[0] = strtolower($templateName[0]);
            $this->template = $templateName . '.phtml';
        }
        
        if ($this->disableWrapper) {
            $boxPath = 'box/' . $this->template;
            return self::$view->render($boxPath);
        }
        return self::$view->render('box/box.phtml');
    }
}