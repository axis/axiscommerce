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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Axis_Location_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Model_Geozone_Zone extends Axis_Db_Table
{
    protected $_name = 'location_geozone_zone';

    /**
     *
     * @param array $data
     * @param mixed $where
     * @return mixed
     */
    public function update(array $data, $where)
    {
        if (empty($data['modified_on'])) {
            $data['modified_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::update($data, $where);
    }

    /**
     *
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        if (empty($data['created_on'])) {
            $data['created_on'] = Axis_Date::now()->toSQLString();
        }
        if (empty($data['modified_on'])) {
            $data['modified_on'] = Axis_Date::now()->toSQLString();
        }
        return parent::insert($data);
    }
    
    /**
     * Checks is geozone includes country and zone
     *  
     * @param int $geozoneId
     * @param int $countryId
     * @param int $zoneId
     * @return bool
     */
    public function inGeozone($geozoneId, $countryId, $zoneId)
    {
        $where = array(
            $this->getAdapter()->quoteInto('geozone_id = ?', $geozoneId),
            $this->getAdapter()->quoteInto('country_id IN(0, ?)', $countryId),
            $this->getAdapter()->quoteInto('zone_id IN(0, ?)', $zoneId)
        );
        if (!count($this->fetchAll($where))) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param array $data
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save(array $data)
    {
        $row = $this->find($data['id'])->current();
        unset($data['id']);
        if($row instanceof Axis_Db_Table_Row) {
            $row->setFromArray($data);
        } else {
           $row = $this->createRow($data);
        }
        return $row->save();
    }
}