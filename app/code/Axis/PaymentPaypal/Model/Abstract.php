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
 * @abstract
 */
abstract class Axis_PaymentPaypal_Model_Abstract extends Axis_Method_Payment_Model_Card_Abstract
{
    /**
     * @var Axis_PaymentPaypal_Model_Api_Nvp
     */
    protected $_api;
    
    public function init()
    {
        $this->_api = Axis::single('paymentPaypal/api_nvp', $this->_code);
    }
    
    public function getCheckout()
    {
        return Axis::single('checkout/checkout');
    }

    public function getApi()
    {
        return $this->_api;
    }
    
    public function getLineItemDetails() 
    {
        // @todo кожному продукту його таксу
        $orderTotals = $this->getCheckout()->getTotal()->getCollects();
        $total =  $this->getCheckout()->getTotal()->getTotal();
        $products = $this->getCheckout()->getCart()->getProducts();
        $optionsST = array();
        $optionsLI = array();
        $onetimeSum = 0;
        $onetimeTax = 0;
        $creditsApplied = 0;
        $creditsTax_applied = 0;
        $sumOfLineItems = 0;
        $sumOfLineTax = 0;

        $optionsST['ITEMAMT']     = round($this->getAmountInBaseCurrency($orderTotals['subtotal']['total']), 2);
        $optionsST['TAXAMT']      = round($this->getAmountInBaseCurrency($orderTotals['tax']['total']), 2);
        $optionsST['SHIPPINGAMT'] = round($this->getAmountInBaseCurrency($orderTotals['shipping']['total']), 2);
        $optionsST['AMT']         = round($this->getAmountInBaseCurrency($total), 2);
        $optionsST['HANDLINGAMT'] = 0;

        /*// prepare subtotals
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
          if ($order_totals[$i]['code'] == 'ot_subtotal') $optionsST['ITEMAMT']     = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_tax')      $optionsST['TAXAMT']      = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_shipping') $optionsST['SHIPPINGAMT'] = round($order_totals[$i]['value'],2);
          if ($order_totals[$i]['code'] == 'ot_total')    $optionsST['AMT']         = round($order_totals[$i]['value'],2);
          $optionsST['HANDLINGAMT'] = 0;
          global $$order_totals[$i]['code'];
          if (isset($$order_totals[$i]['code']->credit_class) && $$order_totals[$i]['code']->credit_class == true)
            $creditsApplied += round($order_totals[$i]['value'],2);
          // treat all other OT's as if they're related to handling fees
          if (!in_array($order_totals[$i]['code'], array('ot_total','ot_subtotal','ot_tax','ot_shipping'))
              && !(isset($$order_totals[$i]['code']->credit_class) && $$order_totals[$i]['code']->credit_class == true)) {
              $optionsST['HANDLINGAMT'] += $order_totals[$i]['value'];
          }
        }
         */
        // loop thru all products to display quantity and price. Appends *** if out-of-stock.
        $k = 0;
        foreach ($products as $productId=>$product) {
            $optionsLI["L_NUMBER$k"] = $product['sku'];
            $optionsLI["L_QTY$k"] = (int) $product['quantity'];
            $optionsLI["L_NAME$k"] = $product['name'];
            
            // if there are attributes, loop thru them and add to description
            if (isset($product['attributes']) && sizeof($product['attributes']) > 0) {
                foreach ($product['attributes'] as $attr) {
                    $optionsLI["L_NAME$k"] .= "\n ".$attr['product_option'].': '.$attr['product_option_value'];
                }
            }
            
            $optionsLI["L_AMT$k"] = $product['final_price'];
            //$optionsLI["L_TAXAMT$k"] = $product['tax'];
            
            // track one-time charges
            if (isset($product['onetime_charges']) && $product['onetime_charges'] != 0) {
                $onetimeSum += $product['onetime_charges'];
                //$onetimeTax += $product['tax'];
            }
            
            // Replace & and = with * if found.
            $optionsLI["L_NAME$k"] = str_replace(array('&', '='), '*', $optionsLI["L_NAME$k"]);
            $optionsLI["L_NAME$k"] = strip_tags($optionsLI["L_NAME$k"]);
            
            // reformat properly
            $optionsLI["L_NUMBER$k"] = substr($optionsLI["L_NUMBER$k"], 0, 127);
            $optionsLI["L_NAME$k"] = substr($optionsLI["L_NAME$k"], 0, 127);
            $optionsLI["L_AMT$k"] = $optionsLI["L_AMT$k"];
            //$optionsLI["L_TAXAMT$k"] = round($optionsLI["L_TAXAMT$k"], 2);
            $k++;
        }
        
        if ($onetimeSum > 0) {
            $i++;
            $k++;
            $optionsLI["L_NUMBER$k"] = $k;
            $optionsLI["L_NAME$k"] = 'One-Time Charges';
            $optionsLI["L_AMT$k"] = $onetimeSum;
            $optionsLI["L_TAXAMT$k"] = $onetimeTax;
            $optionsLI["L_QTY$k"] = 1;
        }
        
        // handle discounts such as gift certificates and coupons
        if ($creditsApplied > 0) {
            $optionsST['HANDLINGAMT'] -= $creditsApplied;
        }
        
        // add all one-time charges
        $optionsST['ITEMAMT'] += $onetimeSum;
        
        //ensure things are not negative
        $optionsST['HANDLINGAMT'] = abs(strval($optionsST['HANDLINGAMT']));
        
        // ensure all numbers are non-negative
        if (is_array($optionsST))
            foreach ($optionsST as $key=>$value) {
                $optionsST[$key] = abs(strval($value));
            }
        if (is_array($optionsLI))
            foreach ($optionsLI as $key=>$value) {
                if (strstr($key, 'AMT'))
                    $optionsLI[$key] = abs(strval($value));
            }
        
        // subtotals have to add up to AMT
        // Thus, if there is a discrepancy, make adjustment to HANDLINGAMT:
        $st = $optionsST['ITEMAMT'] + $optionsST['TAXAMT'] + $optionsST['SHIPPINGAMT'] + $optionsST['HANDLINGAMT'];
        if ($st != $optionsST['AMT'])
            $optionsST['HANDLINGAMT'] += strval($optionsST['AMT'] - $st);

         /*
         //PayPal API spec contradicts itself ... and apparently neither of these "requirements" are enforced. 
         //Thus skipping this section for now:
         // according to API specs, these can't be set if they contain zero values, so unset if they are zero:
         if ($optionsST['TAXAMT'] == 0)      unset($optionsST['TAXAMT']);
         if ($optionsST['SHIPPINGAMT'] == 0) unset($optionsST['SHIPPINGAMT']);
         if ($optionsST['HANDLINGAMT'] == 0) unset($optionsST['HANDLINGAMT']);
         // set missing subtotals if they are zero values, since all must be submitted
         if (!isset($optionsST['TAXAMT']))      $optionsST['TAXAMT'] = 0;
         if (!isset($optionsST['SHIPPINGAMT'])) $optionsST['SHIPPINGAMT'] = 0;
         if (!isset($optionsST['HANDLINGAMT'])) $optionsST['HANDLINGAMT'] = 0;
         */

        // Since the PayPal spec can't handle mathematically mismatched values caused by one-time charges,
        // must drop line-item details if any one-time charges apply to this order:
        // if there are any discounts in this order, do NOT supply line-item details
        if ($onetimeSum > 0)
            $optionsLI = array();
        // Do sanity check -- if any of the line-item subtotal math doesn't add up properly, skip line-item details,
        // so that the order can go through even though PayPal isn't being flexible to handle Zen Cart's diversity
        for ($j = 0; $j < $k; $j++) {
            $itemAMT = $optionsLI["L_AMT$j"];
            //  $itemTAX = $optionsLI["L_TAXAMT$j"];
            $itemQTY = $optionsLI["L_QTY$j"];
            $sumOfLineItems += ($itemQTY * $itemAMT);
            // $sumOfLineTax += round(($itemQTY * $itemTAX), 2);
        }
        if ((float) $optionsST['ITEMAMT'] != (float) strval($sumOfLineItems)) {
            $optionsLI = array();
        }
        /*
         if ((float)$optionsST['TAXAMT']  != (float)strval($sumOfLineTax)) {
            $optionsLI = array();
         }
         */

        // if there are any discounts in this order, do not supply subtotals or line-item details
        if (strval($creditsApplied) > 0)
            return array();

        
        // if subtotals are not adding up correctly, then skip sending any line-item or subtotal details to PayPal
        $st = round(strval($optionsST['ITEMAMT'] + $optionsST['TAXAMT'] + $optionsST['SHIPPINGAMT'] + $optionsST['HANDLINGAMT']), 2);
        $stDiff = strval($optionsST['AMT'] - $st);
        $stDiffRounded = strval(abs($st) - abs(round($optionsST['AMT'], 2)));
        
        // tidy up all values so that they comply with proper format (number_format(xxxx,2) for PayPal US use )
        if (is_array($optionsST))
            foreach ($optionsST as $key=>$value) {
                $optionsST[$key] = number_format(abs($value), 2);
            }
        if (is_array($optionsLI))
            foreach ($optionsLI as $key=>$value) {
                if (strstr($key, 'AMT'))
                    $optionsLI[$key] = number_format(abs($value), 2);
            }
        
        if ($stDiffRounded != 0)
            return array(); //die('bad subtotals'); //return array();
        
        if (abs($optionsST['HANDLINGAMT']) == 0)
            unset($optionsST['HANDLINGAMT']);
        
        return array_merge($optionsST, $optionsLI);
	}
	
    /**
     * Retrieve paypal messages from response
     * 
     * @param array $response
     * @param string $filter [optional]
     * @return array
     */
    public function getMessages($response, $filter = 'L_LONGMESSAGE')
    {
        $result = array();
        $i = 0;
        while(isset($response[$filter . $i])) {
            $result[$response['L_SEVERITYCODE' . $i]][] = urldecode($response[$filter . $i])
                . ' ErrorCode:' . $response['L_ERRORCODE' . $i];
            $i++;
        }
        return $result;
    }
}