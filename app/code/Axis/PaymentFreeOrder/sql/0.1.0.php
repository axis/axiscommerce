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
        Axis::single('core/config_field')
            ->add('payment', 'Payment Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('payment/FreeOrder_Standard', 'Payment Methods/Free Order', null, null, array('translation_module' => 'Axis_PaymentFreeOrder'))
            ->add('payment/FreeOrder_Standard/enabled', 'Payment Methods/Free Order/Enabled', '0', 'radio', '',array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('payment/FreeOrder_Standard/title', 'Title', 'Free Order', 'string', 'Title')
            ->add('payment/FreeOrder_Standard/geozone', 'Allowed Payment Zone', '1', 'select', 'Payment method will be available only for selected zone', array('model' => 'Axis_Location_Model_Option_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('payment/FreeOrder_Standard/sortOrder', 'Sort Order', '1', 'string', array('translation_module' => 'Axis_Core'))
            ->add('payment/FreeOrder_Standard/orderStatusId', 'Order Status on payment complete', '1', 'select', 'Set the status of orders made with this payment module to this value', array('model' => 'Axis_Sales_Model_Option_Order_Status', 'translation_module' => 'Axis_Admin'))
            ->add('payment/FreeOrder_Standard/shippings', 'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'Axis_Checkout_Model_Option_Shipping', 'translation_module' => 'Axis_Admin'));
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('payment/FreeOrder_Standard');
        Axis::single('core/config_field')->remove('payment/FreeOrder_Standard');
    }
}