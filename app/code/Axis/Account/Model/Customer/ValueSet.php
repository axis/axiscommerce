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
 * @copyright   Copyright 2008-2011 Axis
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
     * @return mixed Axis_Db_Table_Row|void
     */
    public function save(array $data)
    {
        if (empty($data['name'])) {
            return;
        }
        $row = $this->getRow($data);
        $row->save();
        return $row;
    }
}