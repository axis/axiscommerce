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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Box_Currency extends Axis_Core_Box_Abstract
{
    protected $_title = 'Currency';
    protected $_class = 'box-currency';
    
    public function init()
    {
        $currency = Axis_Collect_Currency::collect();
        if (count($currency) <= 1) {
            return;
        }
        $this->updateData(array(
            'currencyCode' => Axis::single('locale/currency')->getCode(),
            'currency' => $currency
        ));
    }
    
    public function hasContent()
    {
        return $this->hasCurrency();
    }
}