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
 * @package     Axis_Tax
 * @subpackage  Axis_Tax_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Tax
 * @subpackage  Axis_Tax_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tax_Model_Class extends Axis_Db_Table
{
    protected $_name = 'tax_class';

    /**
     * Save data call backend
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        if (!sizeof($data)) {
            return false;
        }
        $row = $this->getRow($data);
        $row->modified_on = Axis_Date::now()->toSQLString();
        if (empty($row->created_on)) {
            $row->created_on = $row->modified_on;
        }
        $row->save();
        return $row;
    }
}
