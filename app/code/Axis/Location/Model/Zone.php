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
 * @package     Axis_Location
 * @subpackage  Axis_Location_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Axis_Location_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Model_Zone extends Axis_Db_Table implements Axis_Config_Option_Array_Interface
{
    protected $_name = 'location_zone';

    /**
     *
     * @param array $row
     * @return mixed Axis_Db_Table_Row|void
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        //before save (validate and throw e ?)
        if (empty($row->code) || empty($row->name)) {
            return;
        }
        $row->save();
        return $row;
    }   
    
    /**
     *
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
       $rows = Axis::single('location/zone')->fetchAll()->toArray();
       $zones = array();
       foreach ($rows as $row) {
            $zones[$row['country_id']][$row['id']] = $row['name'];
        }
       return $zones;
    }

    /**
     *
     * @static
     * @param int $id
     * @return mixed string|void
     */
    public static function getConfigOptionName($id)
    {
        if (!$id) return '';
        return Axis::single('location/zone')->getNameById($id);
    }
} 