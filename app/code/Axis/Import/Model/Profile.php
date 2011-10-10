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
 * @package     Axis_Import
 * @subpackage  Axis_Import_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Import
 * @subpackage  Axis_Import_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Import_Model_Profile extends Axis_Db_Table
{
    protected $_name = 'import_profile';

    public function getList()
    {
        return $this->select()
            ->order(array('updated_at DESC', 'created_at DESC'))
            ->fetchAll();
    }

    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row  
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        //before save
        $row->updated_at = Axis_Date::now()->toSQLString();
        if (empty($row->created_at)) {
            $row->created_at = $row->updated_at;
        }
        $row->save();
        
        return $row;
    }

    /**
     *
     * @param array $where
     * @return int          The number of rows deleted. 
     */
    public function delete($where)
    {
        $where = $this->getAdapter()->quoteInto('id IN(?)', $where);
        
        return parent::delete($where);
    }
}