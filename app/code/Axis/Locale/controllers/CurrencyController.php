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
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_CurrencyController extends Axis_Core_Controller_Front
{
    public function changeAction()
    {
        $code = $this->_getParam('currency-code', 'USD');
        if (Axis::single('locale/currency')->isExists($code)) {
            Axis::session()->currency = $code;
        }
        $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
    }
}