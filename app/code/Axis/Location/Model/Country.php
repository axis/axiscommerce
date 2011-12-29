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
class Axis_Location_Model_Country extends Axis_Db_Table implements Axis_Config_Option_Array_Interface
{
    protected $_name = 'location_country';

    protected static $_collection = null;
    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        //validate
        $primary = $this->_primary;
        if (!is_array($primary)) {
           $primary = array($primary); 
        }
        $columns = array_diff($this->_getCols(), $primary);
        foreach ($columns as $column) {
            if (empty($row->$column)) {
                //must by throw
                Axis::message()->addError(
                    Axis::translate('core')->__(
                        'Required fields "%s" are missing', $column
                    )
                );
                return false; 
            }
        }
        $row->save();
        
        return $row;
    }
    
    /**
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        if (null === self::$_collection) {
            self::$_collection = Axis::single('location/country')
                ->select(array('id', 'name'))
                ->fetchPairs();
        }
        return self::$_collection;
    }
    
    /**
     *
     * @static
     * @param int $id
     * @return string
     */
    public static function getConfigOptionName($id)
    {
        self::getConfigOptionsArray();
        $return = array();

        foreach(explode(Axis_Config::MULTI_SEPARATOR, $id) as $key) {
            if (array_key_exists($key, self::$_collection)) {
                $return[$key] = isset(self::$_collection[$key]) ?
                        self::$_collection[$key] : '';
            }
        }
        if (count($return) === count($options)) {
            return 'All';
        }
        return implode(", ", $return);
    }
}
