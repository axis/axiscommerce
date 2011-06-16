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
 * @subpackage  Axis_PaymentPaypal_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_PaymentPaypal
 * @subpackage  Axis_PaymentPaypal_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_PaymentPaypal_Model_Standard extends Axis_Method_Payment_Model_Abstract
{
    protected $_code = 'Paypal_Standard';
    protected $_title = 'PayPal Standard';

    public function postProcess(Axis_Sales_Model_Order_Row $order)
    {
        $view = Axis::app()->getBootstrap()->getResource('layout')->getView();
        $delivery = $order->getDelivery();
        $formFields = array(
            'business'          => $this->_config->email,
            'return'            => $view->href('paymentpaypal/standard/success'),
            'cancel_return'     => $view->href('paymentpaypal/standard/cancel'),
            'notify_url'        => $view->href('paymentpaypal/standard/ipn'),
            'invoice'           => $order->id,
            'address_override'  => 1,
            'first_name'        => $delivery->getFirstname(),
            'last_name'         => $delivery->getLastname(),
            'address1'          => $delivery->getStreetAddress(),
            'address2'          => $delivery->getSuburb(),
            'city'              => $delivery->getCity(),
            'state'             => $delivery->getZone()->getCode(),
            'country'           => $delivery->getCountry()->getIsoCode2(),
            'zip'               => $delivery->getPostcode(),
        );
        if ($this->_config->logo) {
            $formFields['cpp_header_image'] = $this->_config->logo;
        }

        if ($this->_config->paymentAction) {
            $formFields['paymentaction'] = strtolower($this->_config->paymentAction);
        }

        $transaciton_type = $this->_config->transactionType;
        /*
        O=aggregate cart amount to paypal
        I=individual items to paypal
        */
        if ($transaciton_type == 'Aggregate Cart') {
            $businessName = $this->_config->name;
            $formFields = array_merge($formFields, array(
                    'cmd'           => '_ext-enter',
                    'redirect_cmd'  => '_xclick',
                    'item_name'     => $businessName ?
                        $businessName :  Axis::config()->core->store->name,
                    'currency_code' => $this->getBaseCurrencyCode(),
                    'amount'        => sprintf('%.2f', $this->getAmountInBaseCurrency(
                        $order->getSubTotal(), $order->currency
                    )),
                ));

            $tax = $order->getTax();
            $shippingTax = $order->getShippingTax();
            $tax = sprintf('%.2f', $tax + $shippingTax);
            if ($tax > 0) {
                $formFields['tax'] = $tax;
            }

        } else {
            $formFields = array_merge($formFields, array(
                'cmd'       => '_cart',
                'upload'    => '1',
            ));
            $products = $order->getProducts();

            if ($products) {
                $i = 1;
                foreach($products as $product) {
                    $formFields = array_merge($formFields, array(
                        'item_name_' . $i   => $product['name'],
                        'item_number_' . $i => $product['sku'],
                        'quantity_' . $i    => intval($product['quantity']),
                        'amount_' . $i      => sprintf('%.2f', $product['final_price']),
                    ));
                    if($product['tax'] > 0) {
                        $formFields = array_merge($formFields, array(
                            'tax_' . $i      => sprintf('%.2f', $product['tax']/*/$item['quantity']*/),
                        ));
                    }
                    $i++;
                }
            }
        }
        $totals = $order->getTotals();

        $shipping = sprintf('%.2f', $totals['shipping']['value']);
        if ($shipping > 0) {
          if ($transaciton_type == 'Aggregate Cart') {
              $formFields['shipping'] = $shipping;
          } else {
              $shippingTax = $totals['shipping_tax']['value'];
              $formFields = array_merge($formFields, array(
                    'item_name_' . $i   => $order->shipping_method,
                    'quantity_' . $i    => 1,
                    'amount_' . $i      => $shipping,
                    'tax_' . $i         => sprintf('%.2f', $shippingTax),
              ));
              $i++;
          }
        }
        $sReq = '';
        $rArr = array();
        foreach ($formFields as $k => $v) {
            /*
            replacing & char with and. otherwise it will break the post
            */
            $value =  str_replace('&', 'and', $v);
            $rArr[$k] =  $value;
            $sReq .= '&' . $k . '=' . $value;
        }

        $this->getStorage()->formFields = $rArr;

        return array(
            'redirect' => $view->href('paymentpaypal/standard/submit')
        );
    }

    /**
     * "processing" actually
     *
     * @param array $post
     * @return null
     */
    public function ipnSubmit($post)
    {
        if (isset($post['module'])) { unset($post['module']);}
        if (isset($post['controller'])) { unset($post['controller']);}
        if (isset($post['action'])) { unset($post['action']);}
        if (isset($post['locale'])) { unset($post['locale']);}

        $request = '';
        foreach($post as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(stripslashes($value));
        }
        //append ipn commdn
        $request .= "&cmd=_notify-validate";
        $request = substr($request, 1);

        $httpClient = new Zend_Http_Client();

        $uri = $this->_config->url . '?' . $request;

        $httpClient->setUri($uri);
        $response = $httpClient->request('POST')->getBody();

        $order = Axis::single('sales/order')->find($post['invoice'])->current();
        if (!$order) {
            return;
        }
        if ($response != 'VERIFIED') {
             /* Canceled_Reversal, Completed, Denied, Expired, Failed
                 Pending, Processed, Refunded, Reversed, Voided*/
            $comment = $post['payment_status'];
            if ($post['payment_status'] == 'Pending') {
                $comment .= ' - ' . $post['pending_reason'];
            } elseif ( ($post['payment_status'] == 'Reversed')
                || ($post['payment_status'] == 'Refunded') ) {

                $comment .= ' - ' . $post['reason_code'];
            }
            $order->addComment(
                Axis::translate('checkout')->__(
                    "Paypal IPN Invalid %s.", $comment
                )
            );
            return;
        }

        if ($post['mc_gross'] != $order->order_total) {
            $order->addComment(
                Axis::translate('checkout')->__(
                    'Order total amount does not match paypal gross total amount'
                )
            );
            return;
        }

        Axis::single('paymentPaypal/standard_order')->insert(array(
           'order_id' => $order->id,
           'trans_id' => $post['txn_id'],
           'status' => $post['payment_status']
        ));

        $message = Axis::translate('checkout')->__(
            "Received IPN verification"
        );
        if ($post['payment_status'] == 'Completed') {
           $message = Axis::translate('checkout')->__(
               "Transaction in sale mode"
           );
        }

        $order->setStatus('processing', $message, true);
    }
}
