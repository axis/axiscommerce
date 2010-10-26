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
 * @package     Axis_Translate
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Translate
 * @subpackage  Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Translate
{
    /**
     *
     * @var Axis_Translate
     */
    protected $_translate;

    /**
     *  @param  string $module
     *  @return string
     */
    public function translate($module = null)
    {
        if (null === $module) {
            if (null !== $this->view->box) {
                //box
                $module = $this->view->box->boxCategory . '_'
                        . $this->view->box->boxModule;
            } elseif (null !== $this->view->module) {
                //controller render
                $module = $this->view->namespace . '_'
                        . $this->view->moduleName;
            } else {
                $module = 'Axis_Core';
            }
        } elseif (false === strpos($module, '_')) {
            $module = 'Axis_' . $module;
        }
        $module = str_replace(' ', '_', ucwords(str_replace('_', ' ', $module)));

        $this->_translate = Axis::translate($module);
        return $this;
    }

    /**
     * @param string $text
     * @param array $args
     * @return string
     */
    public function __()
    {
        return $this->_translate->translate(func_get_args());
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}