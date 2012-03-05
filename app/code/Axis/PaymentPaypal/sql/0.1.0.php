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
 * @package     Axis_PaymentPaypal
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_PaymentPaypal_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = $this->getInstaller();

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('payment_paypal_standard_order')}`;
        CREATE TABLE  `{$installer->getTable('payment_paypal_standard_order')}` (
          `order_id` int(10) unsigned NOT NULL,
          `trans_id` varchar(128) NOT NULL,
          `status` varchar(32) NOT NULL,
          PRIMARY KEY  USING BTREE (`order_id`),
          CONSTRAINT `FK_paymnet_paypalstandard_order_1` FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_order')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ");

        Axis::single('core/config_field')
            ->add('payment', 'Payment Methods', null, null, array('translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Standard', 'Payment Methods/PayPal Standard', null, null, array('translation_module' => 'Axis_PaymentPaypal'))
            ->add('payment/Paypal_Standard/title', 'Payment Methods/PayPal Standard/Title', 'PayPal Standard')
            ->add('payment/Paypal_Standard/name', 'Paypal business name', 'Axis Paypal Standard Payment')
            ->add('payment/Paypal_Standard/email', 'Paypal business email', 'REQUIRED', 'string', '', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/Paypal_Standard/logo', 'Paypal Logo Image URL', 'logo.gif', 'string', 'Maximum image size is 750x90px')
            ->add('payment/Paypal_Standard/sandboxMode', 'Sandbox Mode', 1, 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean'))
            ->add('payment/Paypal_Standard/enabled', 'Enabled', 0 , 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('payment/Paypal_Standard/paymentAction', 'Payment Action', 'Sale', 'select', 'Payment Action: Default: Sale (Sale, Authorization)', array('model' => 'Axis_PaymentPaypal_Model_Api_PaymentAction'))
            ->add('payment/Paypal_Standard/transactionType', 'Transaction Type', 'Aggregate Cart', 'select', 'Transaction Type', array('model' => 'Axis_PaymentPaypal_Model_Standard_TransactionType'))
            ->add('payment/Paypal_Standard/geozone', 'Allowed Payment Zone',  '1', 'select', 'Payment method will be available only for selected zone', array('model' => 'Axis_Location_Model_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Standard/sortOrder', 'Sort Order', '1', 'string', array('translation_module' => 'Axis_Core'))
            ->add('payment/Paypal_Standard/url', 'Paypal url', 'https://www.sandbox.paypal.com/cgi-bin/webscr')
            ->add('payment/Paypal_Standard/debugUrl', 'Paypal debug url', 'https://www.sandbox.paypal.com/cgi-bin/webscr')
            ->add('payment/Paypal_Standard/orderStatusId', 'Order Status on payment complete', '1', 'select', 'Set the status of orders made with this payment module to this value', array('model' => 'Axis_Sales_Model_Order_Status', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Standard/shippings', 'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'Axis_Checkout_Model_Shipping', 'translation_module' => 'Axis_Admin'))

            ->add('payment/Paypal_Direct', 'Payment Methods/Paypal Direct', null, null, array('translation_module' => 'Axis_PaymentPaypal'))
            ->add('payment/Paypal_Direct/title', 'Payment Methods/Paypal Direct/Title', 'PayPal Direct', 'string', 'Title')
            ->add('payment/Paypal_Direct/enabled', 'Enabled', 0, 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('payment/Paypal_Direct/geozone', 'Allowed Payment Zone',  '1', 'select', 'Payment method will be available only for selected zone', array('model' => 'Axis_Location_Model_Geozone', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Direct/sortOrder', 'Sort Order', '1', 'string', array('translation_module' => 'Axis_Core'))
            ->add('payment/Paypal_Direct/orderStatusId', 'Order Status on payment complete', 0, 'select', 'Set the status of orders made with this payment module to this value', array('model' => 'Axis_Sales_Model_Order_Status', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Direct/enabledCvv', 'Accept verification code', '1', 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Direct/creditCard', 'Accepted Credit Cards', 'VISA', 'multiple', 'Credits cards allowed to use with this payment method', array('model' => 'Axis_Sales_Model_Order_CreditCard_Type', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Direct/saveCCAction', 'Save credit card number action', 'last_four', 'select', 'How would you like to save credit card number', array('model' => 'Axis_Sales_Model_Order_CreditCard_SaveNumberType', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Direct/saveCvv', 'Save verification code', '0', 'bool', 'Do you want to save cvv code?', array('translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Direct/server', 'Live or Sandbox', 'sandbox', 'select', 'Live: Used to process Live transactions Sandbox: For developers and testing', array('model' => 'Axis_PaymentPaypal_Model_Api_ServerType'))
            ->add('payment/Paypal_Direct/paymentAction', 'Payment Action', 'Sale', 'select', 'How do you want to obtain payment?', array('model' => 'Axis_PaymentPaypal_Model_Api_PaymentAction'))
            ->add('payment/Paypal_Direct/currency', 'Transaction Currency',  'USD', 'select', 'Which currency should the order be sent to PayPal?   If an unsupported currency is sent to PayPal, it will be auto-converted to USD (or GBP if using UK account)', array('model' => 'Axis_Locale_Model_Option_Currency'))
            ->add('payment/Paypal_Direct/mode', 'PayPal Api Mode',  'nvp', 'select', 'Which PayPal API system should be used for processing?', array('model' => 'Axis_PaymentPaypal_Model_Api_Type'))
            ->add('payment/Paypal_Direct/shippings', 'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'Axis_Checkout_Model_Shipping', 'translation_module' => 'Axis_Admin'))

            ->add('payment/Paypal_Express', 'Payment Methods/Paypal Express', null, null, array('translation_module' => 'Axis_PaymentPaypal'))
            ->add('payment/Paypal_Express/title', 'Payment Methods/Paypal Express/Title', 'PayPal Express', 'string', 'Title')
            ->add('payment/Paypal_Express/enabled', 'Enabled', 0, 'bool', '', array('model'=> 'Axis_Core_Model_Option_Boolean', 'translation_module' => 'Axis_Core'))
            ->add('payment/Paypal_Express/server', 'Live or Sandbox', 'sandbox', 'select', 'Live: Used to process Live transactions Sandbox: For developers and testing', array('model' => 'Axis_PaymentPaypal_Model_Api_ServerType'))
            ->add('payment/Paypal_Express/mode', 'PayPal Api Mode', 'nvp', 'select', 'set mode Paypal Express Checkout payments (nvp, payflow)', array('model' => 'Axis_PaymentPaypal_Model_Api_Type'))
            ->add('payment/Paypal_Express/orderStatusId', 'Order Status on payment complete', '2', 'select', 'Set the status of orders made with this payment module to this value', array('model' => 'Axis_Sales_Model_Order_Status', 'translation_module' => 'Axis_Admin'))
            ->add('payment/Paypal_Express/paymentAction', 'Payment Action', 'Sale', 'select', 'How do you want to obtain payment? Default: Sale (Sale, Order, Authorization)', array('model' => 'Axis_PaymentPaypal_Model_Express_PaymentAction'))
            ->add('payment/Paypal_Express/shippings', 'Disallowed Shippings', '0', 'multiple', 'Selected shipping methods will be not available with this payment method', array('model' => 'Axis_Checkout_Model_Shipping', 'translation_module' => 'Axis_Admin'))

            ->add('payment/payflow', 'Payment Methods/PayPal Payflow Api', null, null, array('translation_module' => 'Axis_PaymentPaypal'))
            ->add('payment/payflow/pfuser', 'Payment Methods/PayPal Payflow Api/PAYFLOW: User', '', 'string', 'If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise it should be the same value as VENDOR. This value is case-sensitive.', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/payflow/pfpartner', 'PAYFLOW: Partner', 'Axis', 'string', 'Your Payflow Partner linked to your Payflow account. This value is case-sensitive.  Typical values: <strong>PayPal</strong> or <strong>Axis</strong>', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/payflow/pfvendor', 'PAYFLOW: Vendor', '', 'string', 'Your merchant login ID that you created when you registered for the Payflow Pro account. This value is case-sensitive.', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/payflow/pfpassword', 'PAYFLOW: Password', '', 'string',  'The 6- to 32-character password that you defined while registering for the account. This value is case-sensitive.', array('model' => 'Axis_Core_Model_Option_Crypt'))

            ->add('payment/nvp', 'Payment Methods/PayPal NVP Api', null, null, array('translation_module' => 'Axis_PaymentPaypal'))
            ->add('payment/nvp/apiusername', 'Payment Methods/PayPal NVP Api/API Signature -- Username', '', 'string', 'The API Username from your PayPal API Signature settings under *API Access*. This value typically looks like an email address and is case-sensitive.', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/nvp/apipassword', 'API Signature -- Password', '', 'string', 'The API Password from your PayPal API Signature settings under *API Access*. This value is a 16-character code and is case-sensitive.', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/nvp/apisignature', 'API Signature -- Signature', '', 'string', 'The API Signature from your PayPal API Signature settings under *API Access*. This value is a 56-character code, and is case-sensitive.', array('model' => 'Axis_Core_Model_Option_Crypt'))
            ->add('payment/nvp/version', 'VERSION', '3.2', 'string', 'Used protocol Paypal version');

        Axis::single('core/page')
            ->add('paymentpaypal/*/*');
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('payment_paypal_standard_order')}`;
        ");

        Axis::single('core/config_value')->remove('payment/Paypal_Standard');
        Axis::single('core/config_field')->remove('payment/Paypal_Standard');
        Axis::single('core/config_value')->remove('payment/Paypal_Direct');
        Axis::single('core/config_field')->remove('payment/Paypal_Direct');
        Axis::single('core/config_value')->remove('payment/Paypal_Express');
        Axis::single('core/config_field')->remove('payment/Paypal_Express');
        Axis::single('core/config_value')->remove('payment/payflow');
        Axis::single('core/config_field')->remove('payment/payflow');
        Axis::single('core/config_value')->remove('payment/nvp');
        Axis::single('core/config_field')->remove('payment/nvp');

        //Axis::single('core/template_box')->remove('Axis_PaymentPaypal_ExpressButton');
    }
}