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
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Method
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Axis_Checkout_Method
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Method_Payment_Model_Card_Abstract extends Axis_Method_Payment_Model_Abstract
{
    /**
     * Save credit card object and custom attributes
     * @see (form.phtml, setPaymentAction)
     * @return bool
     * @param array $data
     */
    public function saveData($data)
    {
        $is_valid = true;
        if (!empty($data['cc_number'])) {
            $is_valid = $this->setCreditCard(
                $data['cc_type'],
                $data['cc_owner'],
                $data['cc_number'],
                $data['cc_expires_year'],
                $data['cc_expires_month'],
                isset($data['cc_cvv']) ? $data['cc_cvv'] : null
            );
            unset($data['cc_type']);
            unset($data['cc_owner']);
            unset($data['cc_number']);
            unset($data['cc_expires_year']);
            unset($data['cc_expires_month']);
            unset($data['cc_cvv']);
        }
        return $is_valid ? parent::saveData($data) : false;
    }

    /**
     * Set payment storage credit card attributes
     * @return bool
     * @param string $ccType
     * @param string $ccOwner
     * @param string $ccNumber
     * @param string $ccExpiresYear
     * @param string $ccExpiresMonth
     * @param string $ccCvv
     * @param string $cc_issue_year[optional]
     * @param string $cc_expires_month[optional]
     */
    public function setCreditCard($ccType, $ccOwner, $ccNumber, $ccExpiresYear,
        $ccExpiresMonth, $ccCvv, $ccIssueYear = null, $ccIssueMonth = null)
    {
        if (empty($ccType) || empty($ccNumber)
            || empty($ccExpiresYear) || empty($ccExpiresMonth) ) {

            Axis::message()->addError(
                Axis::translate('checkout')->__(
                    'Set full Credit Card Information'
            ));
            return false;
        }

        $validator = new Zend_Validate_CreditCard();
        $allowedCcTypes = $this->getCCTypes();
        $validator->setType($allowedCcTypes);

        if (!$validator->isValid($ccNumber)) {
            foreach ($validator->getMessages() as $message) {
                Axis::message()->addError($message);
            }
            return false;
        }


        return $this->getCreditCard()
            ->setCcType($ccType)
            ->setCcOwner($ccOwner)
            ->setCcNumber($ccNumber)
            ->setCcExpiresYear($ccExpiresYear)
            ->setCcExpiresMonth($ccExpiresMonth)
            ->setCcIssueYear($ccIssueYear)
            ->setCcIssueMonth($ccIssueMonth)
            ->setCcCvv($ccCvv) instanceof Axis_CreditCard;
    }

    /**
     * Retruns Payment Credit Card object
     * @return Axis_CreditCard
     */
    public function getCreditCard()
    {
        if (!$this->hasCreditCard()) {
            $this->getStorage()->cc = new Axis_CreditCard();
        }
        return $this->getStorage()->cc;
    }

    /**
     * Checks is CreditCard object was created
     * @return boolean
     */
    public function hasCreditCard()
    {
        return $this->getStorage()->cc instanceof Axis_CreditCard;
    }

    /**
     * Returns allowed credit cards types
     * @return array
     */
    public function getCCTypes()
    {
        $usedTypes = $this->_config->creditCard->toArray();
        $allTypes = Axis_Sales_Model_Order_CreditCard_Type::collect();
        $ret = array();
        foreach ($allTypes as $typeKey => $typeName) {
            if (in_array($typeKey, $usedTypes)) {
                $ret[$typeKey] = $typeName;
            }
        }
        return $ret;
    }

    /**
     * Adds information about credit card to the database
     * and sends it by email if required
     *
     * @param Axis_Sales_Model_Order_Row $order
     * @return void
     */
    public function saveCreditCard(Axis_Sales_Model_Order_Row $order)
    {
        $card   = $this->getCreditCard();
        $number = $card->getCcNumber();

        switch (Axis::config("payment/{$order->payment_method_code}/saveCCAction")) {
            case 'last_four':
                $number = str_repeat('X', (strlen($number) - 4)) .
                    substr($number, -4);
                break;
            case 'first_last_four':
                $number = substr($number, 0, 4) .
                    str_repeat('X', (strlen($number) - 8)) .
                    substr($number, -4);
                break;
            case 'partial_email':
                $number = substr($number, 0, 4) .
                    str_repeat('X', (strlen($number) - 8)) .
                    substr($number, -4);

                try {
                    $numberToSend = $card->getCcNumber();
                    $mail = new Axis_Mail();
                    $mail->setLocale(Axis::config('locale/main/language_admin'));
                    $mail->setConfig(array(
                        'subject' => Axis::translate('sales')->__(
                            'Order #%s. Credit card number'
                        ),
                        'data' => array(
                            'text' => Axis::translate('sales')->__(
                                'Order #%s, Credit card middle digits: %s',
                                $order->number,
                                substr($numberToSend, 4, (strlen($numberToSend) - 8))
                            )
                        ),
                        'from' => Axis_Core_Model_Mail_Boxes::getName(
                            Axis::config('core/company/salesDepartmentEmail')
                        ),
                        'to' => Axis_Core_Model_Mail_Boxes::getName(
                            Axis::config('sales/order/email')
                        )
                    ));
                    $mail->send();
                } catch (Zend_Mail_Transport_Exception $e) {
                }
                break;
            case 'complete':
                break;
            default:
                return true;
        }

        $crypt = Axis_Crypt::factory();
        $data  = array(
            'order_id'         => $order->id,
            'cc_type'          => $crypt->encrypt($card->getCcType()),
            'cc_owner'         => $crypt->encrypt($card->getCcOwner()),
            'cc_number'        => $crypt->encrypt($number),
            'cc_expires_year'  => $crypt->encrypt($card->getCcExpiresYear()),
            'cc_expires_month' => $crypt->encrypt($card->getCcExpiresMonth()),
            'cc_cvv'           => Axis::config()->payment->{$order->payment_method_code}->saveCvv ?
                $crypt->encrypt($card->getCcCvv()) : '',
            'cc_issue_year'    => $crypt->encrypt($card->getCcIssueYear()),
            'cc_issue_month'   => $crypt->encrypt($card->getCcIssueMonth())
        );
        Axis::model('sales/order_creditcard')->save($data);
    }
}
