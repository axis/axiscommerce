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
class Axis_View_Helper_ExtTable
{
    private $_isFirstCall = true;

    public function extTable($name, array $columns, array $rows, $params = array())
    {
        $content = '';
        $postRender = '';
        if (isset($params['postRender']))
            $postRender = $params['postRender'];
        $preRender = '';
        if (isset($params['preRender']))
            $preRender = $params['preRender'];
        $config = '';
        if (isset($params['config']))
            $config = $params['config'];
        $items = '';
        if (isset($params['items']))
            $items = $params['items'];
        $plugin = '';
        if (isset($params['plugin']))
            $plugin = $params['plugin'];
        $src = '';
        if (isset($params['src']))
            $src = $params['src'];

        if ($this->_isFirstCall) {
            $this->_isFirstCall = false;
            $content .= "\n{$src}\n";
        }
        $content .= '<table id="grid-'.$name.'" class="axis-table">'
                 . '<thead><tr>';
        foreach ($columns as $columnTitle) {
            $content .= '<th>'.$columnTitle.'</th>';
        }

        $content .= '</tr></thead><tbody>';

        foreach ($rows as $row ) {
            $content .= '<tr>';
            foreach (array_keys($columns) as $column)
                if (isset($row[$column]))
                    $content .= '<td>' .substr($row[$column], 0, 15). '</td>';
                else
                    $content .= '<td>' . ' '. '</td>';
            $content .= '</tr>';
        }
        $content .= "</tbody></table>\n"
            . '<script type="text/javascript">
            var '.$name.';
            Ext.onReady(function() {
              ' . "\n{$items}\n"
              . $name.' = new Ext.grid.TableGrid("grid-' . $name . '", {
              stripeRows: true, // stripe alternate rows
              viewConfig: {forceFit:true},
              header:true,'.
              "\n{$config}\n".
              '
              width:875,
              bbar:[],
              plugins:[
              ' . "\n{$plugin}\n" . '
              new Ext.ux.grid.Search({
                     mode:"local"
                    ,iconCls:false
                    ,width: 220
                    ,dateFormat:"m/d/Y"
                    ,minLength:2
              })]
            });'
            . "\n{$preRender}\n"
            . $name . '.render();'
            . "\n{$postRender}\n"
            . $name . '.setTitle(' . $name . '.getStore().getTotalCount()+" item(s)");
            });
            </script>';
        return $content;
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}