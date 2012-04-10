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
 * @package     Axis_PaymentCheckMoney
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentCheckMoney_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('payment', 'Payment Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('payment/CheckMoney_Standard',               'Payment Methods/Check & Money Order', null, null, array('translation_module' => 'Axis_PaymentCheckMoney'))
            ->add('payment/CheckMoney_Standard/enabled',       'Payment Methods/Check & Money Order/Enabled', '0', 'radio', '', array('model'=> 'core/option_boolean', 'translation_module' => 'Axis_Core'))
            ->add('payment/CheckMoney_Standard/title',         'Title', 'Check & Money Order', 'text', 'Title')
            ->add('payment/CheckMoney_Standard/geozone',       'Allowed Payment Zone', '1', 'select', 'Payment method will be available only for selected zone', array('model' => 'Axis_Location_Model_Option_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CheckMoney_Standard/sortOrder',     'Sort Order', '1', 'text', array('translation_module' => 'Axis_Core'))
            ->add('payment/CheckMoney_Standard/orderStatusId', 'Order Status on payment complete', '1', 'select', 'Set the status of orders made with this payment module to this value', array('model' => 'Axis_Sales_Model_Option_Order_Status', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CheckMoney_Standard/minOrderTotal', 'Minimum order total amount', '', 'text', array('translation_module' => 'Axis_Admin'))
            ->add('payment/CheckMoney_Standard/maxOrderTotal', 'Maximum order total amount', '', 'text', array('translation_module' => 'Axis_Admin'))
            ->add('payment/CheckMoney_Standard/payTo',         'Make check payable to', 'The Axis Store Company', 'text', 'Who should payments be made payable to?')
            ->add('payment/CheckMoney_Standard/sendCheckTo',   'Send your check to', 'Store Name <br />Address <br />Country <br />Phone', 'textarea', ' This is the Store Name, Address and Phone used on printable documents and displayed online')
            ->add('payment/CheckMoney_Standard/shippings',     'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'Axis_Checkout_Model_Option_Shipping', 'translation_module' => 'Axis_Admin'));
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('payment/CheckMoney_Standard');
        Axis::single('core/config_field')->remove('payment/CheckMoney_Standard');
    }
}