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
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Creditcard extends Axis_Db_Table
{
    protected $_name = 'sales_order_creditcard';
    
    /**
     * Insert or update creditcard order row
     * 
     * @param Axis_CreditCard            $card
     * @param Axis_Sales_Model_Order_Row $order
     * @return boolean
     */
    public function save(Axis_CreditCard $card, Axis_Sales_Model_Order_Row $order)
    {
        $full_number = $card->getCcNumber();
        $ret = true;
        
        switch (Axis::config()->payment->{$order->payment_method_code}->saveCCAction) {
            case 'last_four':
            	$number = str_repeat('X', (strlen($full_number) - 4)) . 
                    substr($full_number, -4);
            	break;
            case 'first_last_four':
                $number = substr($full_number, 0, 4) . 
                    str_repeat('X', (strlen($full_number) - 8)) . 
                    substr($full_number, -4);
                break;
            case 'partial_email':
            	$number = substr($full_number, 0, 4) . 
                    str_repeat('X', (strlen($full_number) - 8)) . 
                    substr($full_number, -4);
                    
            	$message = Axis::translate('sales')->__(
                    'Please direct this email to the Accounting department so that it may'
                    . ' be filed along with the online order it relates to: '
                    . "\n\n" . 'OrderId: %s' . "\n\n" . 'Middle Digits: %s' . "\n\n", 
                    $order->id,
                    substr($full_number, 4, (strlen($full_number) - 8))
                );
                $mail = new Axis_Mail();
                $mail->setConfig(array(
                    'subject' => 'Credit card number. Order ' . $order->id,
                    'data'    => array('text' => $message),
                    'to'      => Axis_Collect_MailBoxes::getName(
                        Axis::config()->sales->order->email
                    ),
                    'from'    => array(
                        'email' => $order->customer_email,
                        'name' => $order->payment_method
                    ),
                    'type' => 'txt'   
                ));
                $ret = $mail->send();
            	break;
            case 'complete':
            	$number = $full_number;
            	break;
            default:
                return true;
        }
        
        $crypt = Axis_Crypt::factory();
        $data = array(
            'cc_type'   => $crypt->encrypt($card->getCcType()),
            'cc_owner'  => $crypt->encrypt($card->getCcOwner()),
            'cc_number' => $crypt->encrypt($number),
            'cc_expires_year' => $crypt->encrypt($card->getCcExpiresYear()),
            'cc_expires_month' => $crypt->encrypt($card->getCcExpiresMonth()),
            'cc_cvv'    => Axis::config()->payment->{$order->payment_method_code}->saveCvv ? 
                $crypt->encrypt($card->getCcCvv()) : '',
            'cc_issue_year'  => $crypt->encrypt($card->getCcIssueYear()),
            'cc_issue_month'  => $crypt->encrypt($card->getCcIssueMonth())
        );
        
        if (!$row = $this->find($order->id)->current()) {
            $row = $this->createRow();
            $row->order_id = $order->id;
        }
        $row->setFromArray($data);
        
        return $ret && $row->save();
    }
}