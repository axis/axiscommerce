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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentPaypal_Model_Express extends Axis_PaymentPaypal_Model_Abstract
{
    protected $_code = 'Paypal_Express';
    protected $_title = 'PayPal Express Checkout';
    protected $_icon = 'https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif';
    protected $_buttonSourceEC = 'Axis-EC_us';
    protected $_storage;

    public function init()
    {
        parent::init();
        if ($this->_config->mode == 'Payflow-UK') {
            $this->_buttonSourceEC = 'Axis-EC_uk';
        } elseif ($this->_config->mode == 'Payflow-US') {
            $this->_buttonSourceEC = 'Axis-ECGW_us';
        }
    }

    public function runSetExpressCheckout()
    {
        $options = array();
        $options['CURRENCY'] = $this->getBaseCurrencyCode();
        $amount = $this->getAmountInBaseCurrency(
            Axis::single('checkout/checkout')->getTotal()->getTotal()
        );

        if (!$amount) {
            $this->log("\n\tFailure SetExpressCheckout. Total order null.\n");
            return false;
        }
        $options['PAYMENTACTION'] = 'Authorization';
        if ($this->_config->transactionMode == 'Order') {
            $options['PAYMENTACTION'] = 'Order';
        }
        if ($this->_config->transactionMode == 'Sale') {
            $options['PAYMENTACTION'] = 'Sale';
        }

        $view = Axis::app()->getBootstrap()->getResource('layout')->getView();
        $returnUrl = $view->href('/paymentpaypal/express-checkout/details', true);
        $cancelUrl = $view->href('/paymentpaypal/express-checkout/cancel', true);

        $delivery = $this->getCheckout()->getDelivery();

        if ($delivery instanceof Axis_Address) {

            $options['ADDROVERRIDE'] = 1;

            // set the address info
            $options['SHIPTONAME'] = $delivery->firstname . ' ' . $delivery->lastname;
            //$options['NAME']    = $delivery->firstname'] . ' ' . $delivery->lastname;
            $options['SHIPTOSTREET'] = $delivery->street_address;
            if (isset($delivery->suburb)) {
                $options['SHIPTOSTREET2'] = $delivery->suburb;
            }
            $options['SHIPTOCITY']        = $delivery->city;
            $options['SHIPTOSTATE']       = null !== $delivery->zone ? $delivery->zone->code : '';
            $options['SHIPTOZIP']         = $delivery->postcode;
            $options['SHIPTOCOUNTRYCODE'] = $delivery->country->isoCode2;
            $options['PHONENUM']          = $delivery->phone;
        }

        if ($this->_config->confirmedAddress) {
            $options['REQCONFIRMSHIPPING'] = 1;
        }

        $options['SOLUTIONTYPE'] = 'SOLE';

        $response = $this->getApi()->SetExpressCheckout(
            number_format($amount, 2),
            $returnUrl,
            $cancelUrl,
            $options
        );

        $this->log(
            "\tRun  SetExpressCheckout \n " .
            "\tAMT :" . number_format($amount, 2) . "\n" .
            "\tReturn URL : " . $returnUrl . "\n" .
            "\tCancel Url : " . $cancelUrl . "\n" .
            "\tOptions : " . Zend_Debug::dump($options, null, false) . "\n" .
            "\tResponse : " . Zend_Debug::dump($response, null, false)
        );

        if ($response['ACK'] != 'Success') {
            $this->log("\n\tFailure SetExpressCheckout\n");
            return false;
        }

        $this->getStorage()->token = preg_replace(
            '/[^0-9.A-Z\-]/', '', urldecode($response['TOKEN'])
        );

        return $response;
    }

    /**
     * Use Token
     *
     */
    public function runGetExpressCheckoutDetails()
    {

        $response = $this->getApi()->GetExpressCheckoutDetails(
            $this->getStorage()->token
        );
        $this->log(
            "Run  GetExpressCheckoutDetails \n"
          . "Token :" . $this->getStorage()->token . "\n"
          . "Respopnse : " . Zend_Debug::dump($response, null, false)
        );
        if ($response['ACK'] != 'Success') {
            $this->log("\n\tFailure GetExpressCheckoutDetails\n");
            return false;
        }

        if (empty($response['PAYERID'])) {
            return false;
        }

        if (!is_array($this->getStorage()->payer)) {
            $this->getStorage()->payer = array();
        }

        $this->getStorage()->payer = array_merge(
            $this->getStorage()->payer,
            array('payer_id'  => $response['PAYERID'],
                'payer_email' => urldecode($response['EMAIL'])
            )
        );

        // Alert customer that they've selected an unconfirmed address at PayPal, and must go back and choose a Confirmed one
        if ($this->_config->confirmedAddress && $response['ADDRESSSTATUS'] != 'Confirmed') {

            $this->log("\n\tFailure GetExpressCheckoutDetails ADDRESSSTATUS not confirmed \n");
            return false;
        }

        if (!isset($response['ADDRESSSTATUS']) || $response['ADDRESSSTATUS'] == 'None' ) {
            return false;
        }

        if (empty($response['SHIPTOSTREET2'])) {
            $response['SHIPTOSTREET2'] = '';
        }
        // accomodate PayPal bug which repeats 1st line of address for 2nd line if 2nd line is empty.
        if ($response['SHIPTOSTREET2'] == $response['SHIPTOSTREET']) {
            $response['SHIPTOSTREET2'] = '';
        }

        // accomodate PayPal bug which incorrectly treats 'Yukon Territory' as YK instead of ISO standard of YT.
        if ($response['SHIPTOSTATE'] == 'YK') {
            $response['SHIPTOSTATE'] = 'YT';
        }
        // same with Newfoundland
        if ($response['SHIPTOSTATE'] == 'NF') {
            $response['SHIPTOSTATE'] = 'NL';
        }

        return $response;
    }

    public function runDoExpressCheckoutPayment()
    {
//        Zend_Debug::dump(__METHOD__);
        $options = $this->getLineItemDetails();
        $delivery = $this->getCheckout()->getDelivery();
        if (!$delivery instanceof Axis_Address) {
            return false;
        }

        $options = array_merge($options, array(
            'SHIPTONAME'   => $delivery->firstname . ' ' . $delivery->lastname,
            'SHIPTOSTREET' => $delivery->street_address,
            'SHIPTOSTREET2'=> (!empty($delivery->suburb)) ? $delivery->suburb : '',
            'SHIPTOCITY'   => $delivery->city,
            'SHIPTOSTATE'  => null !== $delivery->zone ? $delivery->zone->code : '',
            'SHIPTOZIP'    => $delivery->postcode,
            'SHIPTOCOUNTRYCODE' => $delivery->country->isoCode2
        ));
        
        if (is_array($this->getStorage()->payer)
            && count(array_diff(array_values($delivery->toArray()),
                array_values($this->getStorage()->payer))) > 8
            /* 8  this is count($payerinfo)  */) {
            //@todo  getOverrideAddress якщо після повернення з пайпала кор. змінив адресс  доставки
            $options['ADDROVERRIDE'] = 1;
        }

        // if these optional parameters are blank, remove them from transaction
        if (isset($options['SHIPTOSTREET2']) && trim($options['SHIPTOSTREET2']) == '') {

            unset($options['SHIPTOSTREET2']);
        }
        if (isset($options['SHIPTOPHONE']) && trim($options['SHIPTOPHONE']) == '') {

            unset($options['SHIPTOPHONE']);
        }
        // if State is not supplied, repeat the city so that it's not blank, otherwise PayPal croaks
        if ((!isset($options['SHIPTOSTATE']) || trim($options['SHIPTOSTATE']) == '') &&
            !isset($options['SHIPTOCITY']) && $options['SHIPTOCITY'] != '') {

            $options['SHIPTOSTATE'] = $options['SHIPTOCITY'];
        }

        $options['CURRENCY'] = $this->getBaseCurrencyCode();
        $options['BUTTONSOURCE'] = $this->_buttonSourceEC;

        $amount =  number_format($this->getCheckout()->getTotal()->getTotal(), 2);

        $response = $this->getApi()->DoExpressCheckoutPayment(
            $this->getStorage()->token,
            $this->getStorage()->payer['payer_id'],
            $amount, $options
        );

        $this->log(
            "\tRun  DoExpressCheckoutPayment \t\n" .
            "\tToken : " . $this->getStorage()->token . "\n" .
            "\tPayerId : " . $this->getStorage()->payer['payer_id'] . "\n" .
            "\tAMT : " . $amount . "\n" .
            "\tOptions : " . Zend_Debug::dump($options, null, false) .
            "\tRespopnse : " . Zend_Debug::dump($response, null, false)

        );
//        Zend_Debug::dump($this->getStorage()->token);
//        Zend_Debug::dump($this->getStorage()->payer['payer_id']);
//        Zend_Debug::dump($options);
//        Zend_Debug::dump($amount);
//        Zend_Debug::dump($response);
//        die;
        if ($response['ACK'] != 'Success') {
            $this->log("\n\tFailure DoExpressCheckoutPayment\n");
            return false;
        }
        return $response;
    }

    public function getPayPalLoginServer()
    {
        if ($this->_config->server == 'live') {
            // live url
            $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
            // sandbox url
            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            // for UK sandbox -- NOTE: this system is intermittently flakey ... and if it's down, odd redirects occur.
            if ($this->_config->mode == 'payflow') {
                $paypal_url = 'https://test-expresscheckout.paypal.com/cgi-bin/webscr';
            }
        }
        return $paypal_url;
    }

    public function preProcess()
    {        
        $this->getStorage()->markflow = true;
        $view = Axis::app()->getBootstrap()->getResource('layout')->getView();
        $url = $view->href('/paymentpaypal/express-checkout/index', true);
        header("Location: {$url}");
        exit;
    }

    public function clear()
    {
        unset($this->getStorage()->token);
        unset($this->getStorage()->payer);
        unset($this->getStorage()->markflow);
        parent::clear();
    }
}