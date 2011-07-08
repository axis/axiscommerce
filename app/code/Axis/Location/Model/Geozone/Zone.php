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
class Axis_Location_Model_Geozone_Zone extends Axis_Db_Table
{
    protected $_name = 'location_geozone_zone';

    /**
     * Checks is geozone includes country and zone
     * Used by every shipping and payment methods
     *
     * @param int $geozoneId
     * @param int $countryId
     * @param int $zoneId
     * @return bool
     */
    public function inGeozone($geozoneId, $countryId, $zoneId)
    {
        $index = "location/in_geozone_{$geozoneId}_{$countryId}_{$zoneId}";
        if (!Zend_Registry::isRegistered($index)) {
            $where = array(
                $this->getAdapter()->quoteInto('geozone_id = ?', $geozoneId),
                $this->getAdapter()->quoteInto('country_id IN(0, ?)', $countryId),
                $this->getAdapter()->quoteInto('zone_id IN(0, ?)', $zoneId)
            );
            $result = (bool) count($this->fetchAll($where));
            Zend_Registry::set($index, $result);
        }
        return Zend_Registry::get($index);
    }

    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        $row->modified_on = Axis_Date::now()->toSQLString();
        if ($row->created_on) {
            $row->created_on = $row->modified_on;
        }
        $row->save();
        return $row;
    }
}