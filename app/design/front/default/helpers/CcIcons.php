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
 * @subpackage  Axis_View_Helper_Front
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Front
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_CcIcons
{
    protected $_ccTypes = array();
    protected $_valueElementId = null;
    protected $_code = null;

    public function __toString()
    {
        /*
        $content = $this->view->headScript()->appendFile($this->view->baseUrl . '/js/axis/checkout/creditCard.js');
        $content .= $this->view->headLink()->appendStylesheet($this->view->baseUrl . '/skin/front/default/css/cards.css');
         */
        $content = '';
        //@todo not correct insert css style
        $content .= $this->view->formSelect(
            $this->_code . '-CcType', null, null, $this->_ccTypes
        );
        $content .= ' <script type="text/javascript">
                    creditCard' . $this->_code
                . ' = new CreditCard("'
                .  $this->_valueElementId . '", "'
                . $this->_code . '");
                </script>';

        $content .= ' <div class="cards-wrapper">';

        foreach (array_keys($this->_ccTypes) as $ccType) {
            $content .= '<img alt="' . $ccType
                     . '" class="card-icon" id="cc-' . strtolower($ccType)
                     . '" src="' . $this->view->skinUrl('images/s.gif') . '"/>';
        }
        $content .= '</div> <script type="text/javascript">'
                 . '$(document).ready(function(){'
                 . '    $(\'.cards-wrapper\').show();'
                 . '});'
                 .'</script>';
        return $content;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function ccIcons($ccTypes = array(), $code = null)
    {
        $this->_ccTypes = $ccTypes;
        $this->_code = $code;
        $this->_valueElementId = $code . '-CcNumber';
        $this->_code = $code;
        return $this;
    }

}