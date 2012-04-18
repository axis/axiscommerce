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
 * @package     Axis_PaymentFreeOrder
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentFreeOrder_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_builder')
            ->section('payment', 'Payment Methods')
                ->setTranslation('Axis_Admin')
                ->section('FreeOrder_Standard', 'Free Order')
                    ->setTranslation('Axis_PaymentFreeOrder')
                    ->option('enabled', 'Enabled')
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('title', 'Title', 'Free Order')
                        ->setDescription('Title')
                    ->option('geozone', 'Allowed Payment Zone', '1')
                        ->setType('select')
                        ->setDescription('Payment method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('sortOrder', 'Sort Order', '1')
                        ->setTranslation('Axis_Core')
                    ->option('orderStatusId', 'Order Status on payment complete', '1')
                        ->setType('select')
                        ->setDescription('Set the status of orders made with this payment module to this value')
                        ->setModel('sales/option_order_status')
                        ->setTranslation('Axis_Admin')
                    ->option('shippings', 'Disallowed Shippings')
                        ->setType('multiple')
                        ->setDescription('Selected shipping methods will be not available with this payment method')
                        ->setModel('checkout/option_shipping')
                        ->setTranslation('Axis_Admin')

            ->section('/');
    }

    public function down()
    {
        Axis::single('core/config_builder')->remove('payment/FreeOrder_Standard');
    }
}