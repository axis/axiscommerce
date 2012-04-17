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

        Axis::single('core/config_builder')
            ->container('payment', 'Payment Methods')
                ->setTranslation('Axis_Admin')
                ->container('AuthorizenetAim_Standard', 'Authorize.Net Aim')
                    ->setTranslation('Axis_PaymentAuthorizenetAim')
                    ->option('enabled', 'Enabled')
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('title', 'Title', 'Authorize.Net')
                    ->option('shippings', 'Disallowed Shippings')
                        ->setType('multiple')
                        ->setDescription('Selected shipping methods will be not available with this payment method')
                        ->setModel('checkout/option_shipping')
                        ->setTranslation('Axis_Admin')
                    ->option('creditCard', 'Accepted Credit Cards', 'VISA')
                        ->setType('multiple')
                        ->setDescription('Credits cards allowed to use with this payment method')
                        ->setModel('sales/option_order_creditCard_type')
                        ->setTranslation('Axis_Admin')
                    ->option('geozone', 'Allowed Payment Zone', 1)
                        ->setType('select')
                        ->setDescription('Payment method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('sortOrder', 'Sort Order', '1')
                        ->setTranslation('Axis_Core')
                    ->option('orderStatusId', 'Order Status on payment complete', 2)
                        ->setType('select')
                        ->setDescription('Set the status of orders made with this payment module to this value')
                        ->setModel('sales/option_order_status')
                        ->setTranslation('Axis_Admin')
                    ->option('authorizationType', 'Authorization Type', 'authorize')
                        ->setType('select')
                        ->setDescription('Do you want submitted credit card transactions to be authorized only, or authorized and captured?')
                        ->setModel('paymentAuthorizenetAim/option_standard_authorizationType')
                    ->option('xLogin', 'Login Id')
                        ->setDescription('The API Login ID used for the Authorize.net service')
                        ->setModel('core/option_crypt')
                    ->option('xTransactionKey', 'Transaction Key')
                        ->setDescription('Transaction Key used for encrypting TP data (See your Authorizenet Account->Security Settings->API Login ID and Transaction Key for details.)')
                        ->setModel('core/option_crypt')
                    ->option('gateway', 'Gateway URL', 'https://test.authorize.net/gateway/transact.dll')
                    ->option('emailCustomer', 'Customer Notification')
                        ->setType('radio')
                        ->setDescription('Should Authorize.Net email a receipt to the customer?')
                        ->setModel('core/option_boolean')
                    ->option('emailMerchant', 'Merchant Email')
                    ->option('test', 'Test Mode')
                        ->setType('radio')
                        ->setModel('core/option_boolean')
            ->section('/');
    }

    public function down()
    {
        Axis::single('core/config_value')->remove('payment/AuthorizenetAim_Standard');
        Axis::single('core/config_field')->remove('payment/AuthorizenetAim_Standard');
    }
}