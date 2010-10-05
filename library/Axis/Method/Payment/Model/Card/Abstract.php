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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Checkout
 * @subpackage  Method
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
        if (isset($data[$this->_code . '-CcNumber'])) {
            $is_valid = $this->setCreditCard(
                $data[$this->_code . '-CcType'],
                $data[$this->_code . '-CcOwner'],
                $data[$this->_code . '-CcNumber'],
                $data[$this->_code . '-CcExpiresYear'],
                $data[$this->_code . '-CcExpiresMonth'],
                isset($data[$this->_code . '-CcCvv']) ? $data[$this->_code . '-CcCvv'] : null
            );
            unset($data[$this->_code . '-CcType']);
            unset($data[$this->_code . '-CcOwner']);
            unset($data[$this->_code . '-CcNumber']);
            unset($data[$this->_code . '-CcExpiresYear']);
            unset($data[$this->_code . '-CcExpiresMonth']);
            unset($data[$this->_code . '-CcCvv']);
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
        $allTypes = Axis_Collect_CreditCard::collect();
        $ret = array();
        foreach ($allTypes as $typeKey => $typeName) {
            if (in_array($typeKey, $usedTypes)) {
                $ret[$typeKey] = $typeName;
            }
        }
        return $ret;
    }
}