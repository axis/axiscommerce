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
 * @package     Axis_Validate
 * @subpackage  Axis_Validate_Validator
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Validate
 * @subpackage  Axis_Validate_Validator
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Form_Validate_AddressId extends Zend_Validate_Abstract
{
    public function isValid($addressId)
    {
        $row = Axis::single('account/customer_address')
            ->find($addressId)
            ->current();

        if (!$row instanceof Axis_Db_Table_Row) {
            return true;
        }

        if ($row->customer_id == Axis::getCustomerId()) {
            return  true;
        }

        $this->_messages[] = Axis::translate('account')->__(
            'Invalid address data. Try again.'
        );
        return false;
    }
}