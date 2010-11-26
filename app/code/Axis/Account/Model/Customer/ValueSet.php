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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_ValueSet extends Axis_Db_Table 
{
    protected $_name = 'account_customer_valueset';

    /**
     *
     * @return array
     */
    public function getValueSets()
    {
        return $this->fetchAll()->toArray();
    }

    /**
     *
     * @param array $data
     * @return mixed (bool|int)
     */
    public function save($data)
    {
        if (!sizeof($data))
            return false;
        
        $row = array(
            'name' => $data['valueSetName']
        );
        
        $validator = new Zend_Validate_Digits();
        
        if ($validator->isValid($data['valueSetId'])) {
            $valuesetId = $this->update(
                $row, $this->getAdapter()->quoteInto('id = ?', $data['valueSetId'])
            );
        } else {
            $valuesetId = $this->insert($row);
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        return $valuesetId;
    }
}