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
 * @subpackage  Axis_View_Helper_Admin
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Admin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Menu
{
    private $_menu = '';

    protected function _recursive(array $array, $id = null, $content = '', $translationModule = 'Axis_Admin')
    {
        if (null === $id) {
            $content = '<ul id="nav">';
        } else {
            $content = '<ul>';
        }

        $arr = array();
        foreach ($array as $item) {
            if ($item['parent_id'] === $id
                && !isset($arr[$item['sort_order']])) {

                $arr[$item['sort_order']] = $item;
            }
        }
        ksort($arr);
        foreach ($arr as $item) {
            $submenu = '';
            if (intval($item['has_children']) == 1) {
                $submenu = ' submenu';
            }
            $content .= '<li class="level' . $item['lvl'] . $submenu . '">';

            if (null !== $item['translation_module']) {
                $translationModule = $item['translation_module'];
            }
            $liInner = '<span>' . Axis::translate($translationModule)->__($item['title']) . '</span>';
            if (null !== $item['link']) {
                $liInner = '<a href="' . $this->view->href($item['link']) . '">' . $liInner . '</a>';
            }
            $content .= $liInner;
            if (intval($item['has_children']) == 1) {
                $content .= $this->_recursive($array, $item['id'], $content, $translationModule);
            }
            $content .= '</li>';
        }
        unset($arr);
        return $content . '</ul>';
    }

    public function menu()
    {
        $this->_menu = $this->_recursive(
            Axis::single('admin/menu')->getList()
        );
        return $this;
    }

    public function __toString()
    {
            return $this->_menu;
    }

    public function setView($view)
    {
            $this->view = $view;
    }
}