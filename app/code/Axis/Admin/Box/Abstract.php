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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Admin_Box_Abstract extends Axis_Core_Box_Abstract
{
    public function render()
    {
        if (!$this->_enabled || !$this->_beforeRender()) {

            return '';
        }
        $this->getView()->box = $this;

        $path = 'box/box.phtml';
        if ($this->disable_wrapper) {
            $path = 'box/' . $this->getTemplate();
        }
        return $this->getView()->render($path);
    }

    public function getTemplate()
    {
        $template = $this->getData('template');
        if (empty($template)) {
            if (false === function_exists('lcfirst') ) {
                function lcfirst($str) {
                    return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
                }
            }
            $template = lcfirst($this->getData('box_name')) . '.phtml';
            $this->template = $template;
        }
        return $template;
    }
}