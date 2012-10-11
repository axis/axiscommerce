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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Checkout_Upgrade_0_2_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.1';
    protected $_info = 'Ajax reload options added';

    public function up()
    {
        $this->getConfigBuilder()
            ->section('checkout')
                ->section('onestep_ajax', 'Onestep Ajax Requests')
                    ->option('billing_address', 'Reload when Billing Address was Changed', true)
                        ->setType('radio')
                        ->setDescription("You can disable this option if you don't use different payment methods for different addresses")
                        ->setModel('core/option_boolean')
                    ->option('delivery_address', 'Reload when Delivery Address was Changed', true)
                        ->setType('radio')
                        ->setDescription("You can disable this option if you don't use different shipping methods or shipping taxes for different addresses")
                        ->setModel('core/option_boolean')
                    ->option('shipping_method', 'Reload when Shipping Method was Changed', true)
                        ->setType('radio')
                        ->setDescription("You can disable this option if all of your shipping methods have equal pricing, and if you don't have dependency between available payment methods and shipping method")
                        ->setModel('core/option_boolean')
                    ->option('payment_method', 'Reload when Payment Method was Changed', true)
                        ->setType('radio')
                        ->setDescription("You can disable this option if you don't have dependency between available shipping methods and payment method")
                        ->setModel('core/option_boolean')

            ->section('/');
    }
}