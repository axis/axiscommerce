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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Option_Mail_Event extends Axis_Config_Option_Array_Abstract
{
    /**
     *
     * @var const array
     */
    static protected $_events = array(

        'contact_us'                   => 'Contact Us',//
        'default'                      => 'Default', //
        'forgot_password'              => 'Forgot password', //
        'account_new-owner'            => 'New account store owner notice',
        'account_new-customer'         => 'New account congratulation',
        'order_new-owner'              => 'Order create store owner notice',
        'order_new-customer'           => 'Order create congratulation',
        'change_order_status-customer' => 'Order status change'
    );

    /**
     *
     * @return array
     */
    protected function _loadCollection()
    {
        return self::$_events;
    }
}