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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Option_Cart_Redirect extends Axis_Config_Option_Array_Abstract
{   
    const REFERER          = 'referer';
    const CHECKOUT_CART    = 'checkout/cart';
    const CHECKOUT_ONESTEP = 'checkout/onestep';

    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return array(
            self::REFERER          => ucfirst(self::REFERER),
            self::CHECKOUT_CART    => ucfirst(self::CHECKOUT_CART),
            self::CHECKOUT_ONESTEP => ucfirst(self::CHECKOUT_ONESTEP) 
        );
    }
    
    /**
     *
     * @static
     * @return const array
     */
    public static function getDeafult()
    {
        return self::CHECKOUT_ONESTEP;
    }
}