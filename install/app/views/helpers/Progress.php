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
 * @package     Axis_Install
 * @subpackage  Axis_View_Helper_Install
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Install
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Progress
{
    /**
     *
     * @return string
     */
    public function progress()
    {
        $install = Axis_Install_Model_Wizard::getInstance();
        $steps = $install->getSteps();
        $current = $install->getCurrent();

        $html = '<ul class=\'install-progress\'>';
        foreach ($steps as $id => $step) {
            if ($current == $id)
                $html .= '<li class="active">' . Axis::translate('install')->__($step) . '</li>';
            else
                $html .= '<li>' . Axis::translate('install')->__($step) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}