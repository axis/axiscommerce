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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Box_Navigation extends Axis_Core_Box_Abstract
{
    protected $_title = 'Pages';
    protected $_class = 'box-cms-page';
    
    public function init()
    {
        $categories = Axis::single('cms/category')->getRootCategories();
        $pages      = Axis::single('cms/page')->getBoxPages();
        if (!count($categories) && !count($pages)) {
            return false;
        }
        $this->setFromArray(array(
            'categories' => $categories,
            'pages'      => $pages
        ));
        return true;
    }

    protected function _beforeRender()
    {
        return $this->hasCategories() || $this->hasPages();
    }
}