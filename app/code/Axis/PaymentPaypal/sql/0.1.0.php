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
 * @copyright   Copyright 2008-2012 Axis
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

        $this->getConfigBuilder()
            ->section('payment', 'Payment Methods')
                ->setTranslation('Axis_Admin')
                ->section('Paypal_Standard', 'PayPal Standard')
                    ->setTranslation('Axis_PaymentPaypal')
                    ->option('title', 'Title', 'PayPal Standard')
                    ->option('name', 'Paypal business name', 'Axis Paypal Standard Payment')
                    ->option('email', 'Paypal business email', 'REQUIRED')
                        ->setModel('core/option_crypt')
                    ->option('logo', 'Paypal Logo Image URL', 'logo.gif')
                        ->setDescription('Maximum image size is 750x90px')
                    ->option('sandboxMode', 'Sandbox Mode', true)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                    ->option('enabled', 'Enabled', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('paymentAction', 'Payment Action')
                        ->setValue(Axis_PaymentPaypal_Model_Option_PaymentAction::SALE)
                        ->setType('select')
                        ->setDescription('Payment Action: Default: Sale (Sale, Authorization)')
                        ->setModel('paymentPaypal/option_paymentAction')
                    ->option('transactionType', 'Transaction Type')
                        ->setValue(Axis_PaymentPaypal_Model_Option_Standard_TransactionType::AGGREGATE)
                        ->setType('select')
                        ->setDescription('Transaction Type')
                        ->setModel('paymentPaypal/option_standard_transactionType')
                    ->option('geozone', 'Allowed Payment Zone', 1)
                        ->setType('select')
                        ->setDescription('Payment method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('sortOrder', 'Sort Order', 1)
                        ->setTranslation('Axis_Core')
                    ->option('url', 'Paypal url', 'https://www.sandbox.paypal.com/cgi-bin/webscr')
                    ->option('debugUrl', 'Paypal debug url', 'https://www.sandbox.paypal.com/cgi-bin/webscr')
                    ->option('orderStatusId', 'Order Status on payment complete', 1)
//                        @todo->setValue(Axis_Sales_Model_Option_Order_Status::PENDING)
                        ->setType('select')
                        ->setDescription('Set the status of orders made with this payment module to this value')
                        ->setModel('sales/option_order_status')
                        ->setTranslation('Axis_Admin')
                    ->option('shippings', 'Disallowed Shippings')
                        ->setType('multiple')
                        ->setDescription('Selected shipping methods will be not available with this payment method')
                        ->setModel('checkout/option_shipping')
                        ->setTranslation('Axis_Admin')
                ->section('/Paypal_Standard')
            
                ->section('Paypal_Direct', 'Paypal Direct')
                    ->setTranslation('Axis_PaymentPaypal')
                    ->option('title', 'Title', 'PayPal Direct')
                        ->setDescription('Title')
                    ->option('enabled', 'Enabled', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('geozone', 'Allowed Payment Zone', 1)
                        ->setType('select')
                        ->setDescription('Payment method will be available only for selected zone')
                        ->setModel('location/option_geozone')
                        ->setTranslation('Axis_Admin')
                    ->option('sortOrder', 'Sort Order', 1)
                        ->setTranslation('Axis_Core')
                    ->option('orderStatusId', 'Order Status on payment complete')
                        ->setType('select')
                        ->setDescription('Set the status of orders made with this payment module to this value')
                        ->setModel('sales/option_order_status')
                        ->setTranslation('Axis_Admin')
                    ->option('enabledCvv', 'Accept verification code', true)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Admin')
                    ->option('creditCard', 'Accepted Credit Cards')
                        ->setValue('VISA,MASTERCARD,AMERICAN_EXPRESS')
                        ->setType('multiple')
                        ->setDescription('Credits cards allowed to use with this payment method')
                        ->setModel('sales/option_order_creditCard_type')
                        ->setTranslation('Axis_Admin')
                    ->option('saveCCAction', 'Save credit card number action', 'last_four')
                        ->setType('select')
                        ->setDescription('How would you like to save credit card number')
                        ->setModel('sales/option_order_creditCard_saveNumberType')
                        ->setTranslation('Axis_Admin')
                    ->option('saveCvv', 'Save verification code', false)
                        ->setType('radio')
                        ->setDescription('Do you want to save cvv code?')
                        ->setTranslation('Axis_Admin')
                    ->option('server', 'Live or Sandbox')
                        ->setValue(Axis_PaymentPaypal_Model_Option_ServerType::SANDBOX)
                        ->setType('select')
                        ->setDescription('Live: Used to process Live transactions Sandbox: For developers and testing')
                        ->setModel('paymentPaypal/option_serverType')
                    ->option('paymentAction', 'Payment Action')
                        ->setValue(Axis_PaymentPaypal_Model_Option_PaymentAction::SALE)
                        ->setType('select')
                        ->setDescription('How do you want to obtain payment?')
                        ->setModel('paymentPaypal/option_paymentAction')
                    ->option('currency', 'Transaction Currency', 'USD')
                        ->setType('select')
                        ->setDescription('Which currency should the order be sent to PayPal?   If an unsupported currency is sent to PayPal, it will be auto-converted to USD (or GBP if using UK account)')
                        ->setModel('locale/option_currency')
                    ->option('mode', 'PayPal Api Mode')
                        ->setValue(Axis_PaymentPaypal_Model_Option_Type::NVP)
                        ->setType('select')
                        ->setDescription('Which PayPal API system should be used for processing?')
                        ->setModel('paymentPaypal/option_type')
                    ->option('shippings', 'Disallowed Shippings')
                        ->setType('multiple')
                        ->setDescription('Selected shipping methods will be not available with this payment method')
                        ->setModel('checkout/option_shipping')
                        ->setTranslation('Axis_Admin')
                ->section('/Paypal_Direct')
            
                ->section('Paypal_Express', 'Paypal Express')
                    ->setTranslation('Axis_PaymentPaypal')
                    ->option('title', 'Title', 'PayPal Express')
                        ->setDescription('Title')
                    ->option('enabled', 'Enabled', false)
                        ->setType('radio')
                        ->setModel('core/option_boolean')
                        ->setTranslation('Axis_Core')
                    ->option('server', 'Live or Sandbox')
                        ->setValue(Axis_PaymentPaypal_Model_Option_ServerType::SANDBOX)
                        ->setType('select')
                        ->setDescription('Live: Used to process Live transactions Sandbox: For developers and testing')
                        ->setModel('paymentPaypal/option_serverType')
                    ->option('mode', 'PayPal Api Mode')
                        ->setValue(Axis_PaymentPaypal_Model_Option_Type::NVP)
                        ->setType('select')
                        ->setDescription('set mode Paypal Express Checkout payments (nvp, payflow)')
                        ->setModel('paymentPaypal/option_type')
                    ->option('orderStatusId', 'Order Status on payment complete', '2')
//                        @todo->setValue(Axis_Sales_Model_Option_Order_Status::PROCCESSING)
                        ->setType('select')
                        ->setDescription('Set the status of orders made with this payment module to this value')
                        ->setModel('sales/option_order_status')
                        ->setTranslation('Axis_Admin')
                    ->option('paymentAction', 'Payment Action')
                        ->setValue(Axis_PaymentPaypal_Model_Option_PaymentAction::SALE)
                        ->setType('select')
                        ->setDescription('How do you want to obtain payment? Default: Sale (Sale, Order, Authorization)')
                        ->setModel('paymentPaypal/option_express_paymentAction')
                    ->option('shippings', 'Disallowed Shippings')
                        ->setType('multiple')
                        ->setDescription('Selected shipping methods will be not available with this payment method')
                        ->setModel('checkout/option_shipping')
                        ->setTranslation('Axis_Admin')
                ->section('/Paypal_Express')
            
                ->section('payflow', 'PayPal Payflow Api')
                    ->setTranslation('Axis_PaymentPaypal')
                    ->option('pfuser', 'PAYFLOW: User')
                        ->setDescription('If you set up one or more additional users on the account, this value is the ID of the user authorized to process transactions. Otherwise it should be the same value as VENDOR. This value is case-sensitive.')
                        ->setModel('core/option_crypt')
                    ->option('pfpartner', 'PAYFLOW: Partner', 'Axis')
                        ->setDescription('Your Payflow Partner linked to your Payflow account. This value is case-sensitive.  Typical values: <strong>PayPal</strong> or <strong>Axis</strong>')
                        ->setModel('core/option_crypt')
                    ->option('pfvendor', 'PAYFLOW: Vendor')
                        ->setDescription('Your merchant login ID that you created when you registered for the Payflow Pro account. This value is case-sensitive.')
                        ->setModel('core/option_crypt')
                    ->option('pfpassword', 'PAYFLOW: Password')
                        ->setDescription('The 6- to 32-character password that you defined while registering for the account. This value is case-sensitive.')
                        ->setModel('core/option_crypt')
                ->section('/payflow')
            
                ->section('nvp', 'PayPal NVP Api')
                    ->setTranslation('Axis_PaymentPaypal')
                    ->option('apiusername', 'API Signature -- Username')
                        ->setDescription('The API Username from your PayPal API Signature settings under *API Access*. This value typically looks like an email address and is case-sensitive.')
                        ->setModel('core/option_crypt')
                    ->option('apipassword', 'API Signature -- Password')
                        ->setDescription('The API Password from your PayPal API Signature settings under *API Access*. This value is a 16-character code and is case-sensitive.')
                        ->setModel('core/option_crypt')
                    ->option('apisignature', 'API Signature -- Signature')
                        ->setDescription('The API Signature from your PayPal API Signature settings under *API Access*. This value is a 56-character code, and is case-sensitive.')
                        ->setModel('core/option_crypt')
                    ->option('version', 'VERSION', '3.2')
                        ->setDescription('Used protocol Paypal version')

            ->section('/');

        Axis::single('core/page')
            ->add('paymentpaypal/*/*');
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('payment_paypal_standard_order')}`;
        ");

        $this->getConfigBuilder()
            ->remove('payment/Paypal_Standard')
            ->remove('payment/Paypal_Direct')
            ->remove('payment/Paypal_Express')
            ->remove('payment/payflow')
            ->remove('payment/nvp');

        //Axis::single('core/template_box')->remove('Axis_PaymentPaypal_ExpressButton');
    }
}