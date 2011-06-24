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
 * @subpackage  Axis_Checkout_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Box_Cart extends Axis_Core_Box_Abstract
{
    protected $_title = 'Shopping Cart';
    protected $_class = 'box-shopping-cart';
    protected $_url = 'checkout/cart';

    public function init()
    {
        $modelCheckoutCart = Axis::single('checkout/cart');
        $this->setFromArray(array(
            'items' => $modelCheckoutCart->getProducts(),
            'sum'   => $modelCheckoutCart->getTotalPrice(),
            'count' => $modelCheckoutCart->getCount()
        ));
        return true;
    }
}
