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
 * @package     Axis_ShippingFlat
 * @subpackage  Axis_ShippingFlat_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingFlat
 * @subpackage  Axis_ShippingFlat_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
class Axis_ShippingFlat_Model_Option_Standard_Service extends Axis_Config_Option_Array_Abstract
{   
    const PER_ORDER = 'Per Order';
    const PER_ITEM  = 'Per Item';
    
    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::PER_ORDER => 'Per Order',
            self::PER_ITEM  => 'Per Item'
        );
    }
}