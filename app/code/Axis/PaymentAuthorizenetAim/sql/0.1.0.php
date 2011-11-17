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
 * @package     Axis_PaymentAuthorizenetAim
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentAuthorizenetAim_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('payment_authorizenetaim_standard_order')}`;
        CREATE TABLE  `{$installer->getTable('payment_authorizenetaim_standard_order')}` (
          `order_id` int(10) unsigned NOT NULL,
          `trans_id` varchar(128) NOT NULL,
          `cc_type` varchar(32) NOT NULL,
          `cc_owner` varchar(64) NOT NULL,
          `cc_number` varchar(64) NOT NULL,
          `cc_expires` varchar(16) NOT NULL,
          `cc_issue` varchar(16) NOT NULL,
          `x_type` varchar(16) NOT NULL,
          `x_method` varchar(8) NOT NULL,
          PRIMARY KEY  USING BTREE (`order_id`),
          CONSTRAINT `FK_paymnet_authorizenetaimstandard_order_1` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_order')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ");

        Axis::single('core/config_field')
            ->add('payment', 'Payment Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('payment/AuthorizenetAim_Standard', 'Payment Methods/Authorize.Net Aim', null, null, array('translation_module' => 'Axis_PaymentAuthorizenetAim'))
            ->add('payment/AuthorizenetAim_Standard/enabled', 'Payment Methods/Authorize.Net Aim/Enabled', 0, 'bool', array('translation_module' => 'Axis_Core'))
            ->add('payment/AuthorizenetAim_Standard/title', 'Title', 'Authorize.Net')
            ->add('payment/AuthorizenetAim_Standard/shippings', 'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'Shipping', 'translation_module' => 'Axis_Admin'))
            ->add('payment/AuthorizenetAim_Standard/creditCard', 'Accepted Credit Cards', 'VISA', 'multiple', 'Credits cards allowed to use with this payment method', array('model' => 'CreditCard', 'translation_module' => 'Axis_Admin'))
            ->add('payment/AuthorizenetAim_Standard/geozone', 'Allowed Payment Zone', 1, 'select', 'Payment method will be available only for selected zone', array('model' => 'Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('payment/AuthorizenetAim_Standard/sortOrder', 'Sort Order', '1', 'string', array('translation_module' => 'Axis_Core'))
            ->add('payment/AuthorizenetAim_Standard/orderStatusId', 'Order Status on payment complete', 2, 'select', 'Set the status of orders made with this payment module to this value', array('config_options' => '{"2":"processing"}', 'translation_module' => 'Axis_Admin'))
            ->add('payment/AuthorizenetAim_Standard/authorizationType','Authorization Type', '1', 'select', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?', array('config_options' => '{"0":"Authorize","1"Authorize Capture"}'))
            ->add('payment/AuthorizenetAim_Standard/xLogin', 'Login Id', '', 'handler', 'The API Login ID used for the Authorize.net service', array('model' => 'Crypt'))
            ->add('payment/AuthorizenetAim_Standard/xTransactionKey','Transaction Key', '', 'handler', 'Transaction Key used for encrypting TP data (See your Authorizenet Account->Security Settings->API Login ID and Transaction Key for details.)', array('model' => 'Crypt'))
            ->add('payment/AuthorizenetAim_Standard/gateway', 'Gateway URL', 'https://test.authorize.net/gateway/transact.dll')
            ->add('payment/AuthorizenetAim_Standard/emailCustomer', 'Customer Notification', 0, 'bool', 'Should Authorize.Net email a receipt to the customer?')
            ->add('payment/AuthorizenetAim_Standard/emailMerchant', 'Merchant Email', '')
            ->add('payment/AuthorizenetAim_Standard/test', 'Test Mode', 0, 'bool')
            ;
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('payment/AuthorizenetAim_Standard');
        Axis::single('core/config_field')->remove('payment/AuthorizenetAim_Standard');
    }
}