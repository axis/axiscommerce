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
 * @subpackage  Axis_Admin_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Box_Navigation extends Axis_Admin_Box_Abstract
{
    protected $_items = array();

    protected function _construct()
    {
        $this->setData('cache_tags', array('modules'));
    }

    public function addItem(array $item)
    {
        $this->_items = array_merge_recursive($this->_items, $item);
        return $this;
    }

    protected function _beforeRender()
    {
        $this->_items = array(); // forward fix
        Axis::dispatch('admin_box_navigation_prepare', $this);
        $this->menu = new Zend_Navigation($this->_items);
    }

    protected function _getCacheKeyInfo()
    {
        return array(
            Axis::session()->roleId
        );
    }
}
