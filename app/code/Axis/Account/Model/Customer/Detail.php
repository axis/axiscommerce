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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_Detail extends Axis_Db_Table 
{
    protected $_name = 'account_customer_detail';
    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customer_id',
            'refTableClass'     => 'Axis_Account_Model_Customer',
            'refColumns'        => 'id'
        )
    );
    
    /**
     *
     * @param string $nickname
     * @return bool
     */
    public function isExistNickname($nickname)
    {
        $select = $this->select('id')
            ->join('account_customer_field', 'acf.id = acd.customer_field_id')
            ->where('acf.name = ?', 'nickname')
            ->where('acd.data = ?', $nickname)
            ;
        if ($customerId = Axis::getCustomerId()) {
            $select->where('acd.customer_id <> ?', $customerId);
        }
        return (bool) $select->fetchOne();
    }
}