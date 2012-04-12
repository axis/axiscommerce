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
 * @package     Axis_PaymentCreditCard
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentCreditCard_Upgrade_0_1_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.1';
    protected $_info = 'install';

    public function up()
    {
        Axis::single('core/config_field')
            ->add('payment', 'Payment Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard', 'Payment Methods/Save Credit Card', null, null, array('translation_module' => 'Axis_PaymentCreditCard'))
            ->add('payment/CreditCard_Standard/title', 'Payment Methods/Save Credit Card/Title', 'Save Credit Card', 'text', 'Title')
            ->add('payment/CreditCard_Standard/enabled', 'Enabled', 0, 'radio', '', array('model'=> 'core/option_boolean', 'translation_module' => 'Axis_Core'))
            ->add('payment/CreditCard_Standard/geozone', 'Allowed Payment Zone', '1', 'select', 'Payment method will be available only for selected zone', array('model' => 'location/option_geozone', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/sortOrder', 'Sort Order', '1', 'text', array('translation_module' => 'Axis_Core'))
            ->add('payment/CreditCard_Standard/orderStatusId', 'Order Status on payment complete', '1', 'select', 'Set the status of orders made with this payment module to this value', array('model' => 'sales/option_order_status', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/enabledCvv', 'Accept verification code', '1', 'radio', '', array('model'=> 'core/option_boolean', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/saveCvv', 'Save verification code', '0', 'radio', 'Do you want to save cvv code?', array('model'=> 'core/option_boolean', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/minOrderTotal', 'Minimum order total amount', '', 'text', array('translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/maxOrderTotal', 'Maximum order total amount', '', 'text', array('translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/creditCard', 'Accepted Credit Cards', 'VISA', 'multiple', 'Credits cards allowed to use with this payment method', array('model' => 'sales/option_order_creditCard_type', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/saveCCAction', 'Save credit card number action', 'last_four', 'select', 'How would you like to save credit card number', array('model' => 'sales/option_order_creditCard_saveNumberType', 'translation_module' => 'Axis_Admin'))
            ->add('payment/CreditCard_Standard/shippings', 'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'checkout/option_shipping', 'translation_module' => 'Axis_Admin'));
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('payment/CreditCard_Standard');
        Axis::single('core/config_field')->remove('payment/CreditCard_Standard');
    }
}