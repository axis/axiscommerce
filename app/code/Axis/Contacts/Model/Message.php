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
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_Model_Message extends Axis_Db_Table
{
    /**
     * The default table name
     */
    protected $_name = 'contacts_message';
    protected $_selectClass = 'Axis_Contacts_Model_Message_Select';

    /**
     * Adds message to database
     *
     * @param array $data 
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->createRow($data);
        //before save
        $row->created_at = Axis_Date::now()->toSQLString();
        $row->save();
        return $row;
    }
}

