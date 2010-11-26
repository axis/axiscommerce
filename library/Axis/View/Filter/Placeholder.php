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
 * @subpackage  Axis_View_Filter
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Filter
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Filter_Placeholder implements Zend_Filter_Interface
{
    /**
     *
     * @var Zend_View
     */
    protected $_view;

    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * Injects additional scripts and styles,
     * that was linked to headScript after it was outputed
     * This method allows to call scripts from Axis_Box
     *
     * @param string $pageOutput
     */
    public function filter($pageOutput)
    {
        $head = substr($pageOutput, 0, strpos($pageOutput, '</head>'));

        if (empty($head)) {
            return $pageOutput;
        }

        $pageOutput = str_replace(
            array('{{headStyle}}', '{{headLink}}', '{{headScript}}'),
            array(
                $this->_view->headStyle()->toString(),
                $this->_view->headLink()->toString(),
                $this->_view->headScript()->toString()
            ),
            $pageOutput
        );

        return $pageOutput;
    }
}