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
 * @package     Axis_Form
 * @subpackage  Axis_Form_Decorator
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Form
 * @subpackage  Axis_Form_Decorator
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Form_Decorator_LiWrapper extends Zend_Form_Decorator_Abstract
{
    /**
     * Default placement: surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * Render
     *
     * Renders as the following:
     * <li>$content</li>
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $elementName = $this->getElement()->getName();

        return '<li class="li-wrappper" id="' . $elementName . '-wrapper">' . $content . '</li>';
    }
}
