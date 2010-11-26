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
class Axis_View_Helper_SetTabs
{
    public function setTabs(array $tabs, $useBlocks)
    {
        $this->view->columnMode = 'two-columns';
        $content = '<ul id="axis-tabs">';
        foreach ($tabs as $tab) {
            $content .= '<li><a href="#" id="tab-' . $tab['id'] .'">'
                . $this->view->escape($tab['title'])
                . '</a></li>';
        }
        $content .= '</ul>
        <script type="text/javascript">
        Ext.onReady(function(){
            var tabs = new pdTabs("axis-tabs", '. intval($useBlocks) .');
        });
        </script>';
        $this->view->tabs = $content;
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}