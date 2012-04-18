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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Box_Recent extends Axis_Account_Box_Abstract
{
    protected $_title = 'Recent Orders';
    protected $_class = 'box-orders';

    protected function _construct()
    {
        $this->setData('cache_lifetime', 0);
    }

    protected function _beforeRender()
    {
        $orders = Axis::model('sales/order')
            ->getOrdersByCustomer(Axis::getCustomerId());

        if (!count($orders)) {
           return false;
        }
        $this->orders = $orders;
    }
}
