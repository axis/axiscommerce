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
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Box_Account extends Axis_Account_Box_Abstract
{
    protected $_title = 'My Tags';
    protected $_class = 'box-tag';
        
    public function init()
    {
        $tags = Axis::single('tag/customer')->getMyWithWeight();
        if (!count($tags)) {
            return false;
        }
        $this->tags = $tags;
        return true;
    }
    
    public function hasContent()
    {
        return $this->hasTags();
    }
}