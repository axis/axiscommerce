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
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Message
{
    private $_messages = array();

    public function __construct()
    {
        $this->_messages = Axis_Message::getInstance()->get();
    }

    public function message()
    {
        return $this;
    }

    public function __toString()
    {
        $result = "";

        if (count($this->_messages)) {
            $result .= "<div id='messages'>";
            foreach ($this->_messages as $type => $messageArray) {
                $result .= "<ul class='{$type}-msg' title='{$type}'>";
                foreach ($messageArray as $message) {
                    $message = $this->view->escape($message);
                    $result .= "<li>{$message}</li>";
                }
                $result .= "</ul>";
            }
            $result .= "</div>";

            $this->_messages = array();
        }

        return $result;
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}