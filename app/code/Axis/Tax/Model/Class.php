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
    /**
     * The default table name
     */
    protected $_name = 'tax_class';

    public function update(array $data, $where)
    {
        if (empty($data['modified_on'])) {
            $data['modified_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::update($data, $where);
    }
    
    public function insert(array $data)
    {
        if (empty($data['created_on'])) {
            $data['created_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::insert($data);
    }

    /**
     * Save data call backend
     * @param array $data
     */
    public function save($data)
    {
        if (!sizeof($data)) {
            return false;
        }
        $flag = true;
        foreach ($data as $id => $row) {
            if (isset($row['id'])) {// update
                $flag = $flag && (bool) $this->update(
                    array(
                        'name' => $row['name'],
                        'description' => $row['description']
                    ),
                    'id = ' . intval($id)
                );
                continue;
            }

            $flag = $flag && (bool) $this->insert(array(
                'name' => $row['name'],
                'description' => $row['description']
            ));
        }
        return $flag;
    }
}
