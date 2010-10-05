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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Checkout_Model_Total_Shipping extends Axis_Checkout_Model_Total_Abstract
{
    protected $_code = 'shipping';
    protected $_title = 'Shipping';

    public function collect(Axis_Checkout_Model_Total $total)
    {
        $checkout = Axis::single('checkout/checkout');
        $shipping = $checkout->shipping();
        if (null === $shipping) {
            return false;
        }
        $type = $shipping->getType(
            $checkout->getShippingRequest(), $checkout->getShippingMethodCode()
        );
        $total->addCollect(array(
            'code'      => $this->getCode(),
            'title'     => $this->getTitle(),
            'total'     => $type['price'],
            'sortOrder' => $this->_config->sortOrder
        ));
    }
}