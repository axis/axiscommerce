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
class Axis_Location_Model_Geozone extends Axis_Db_Table implements Axis_Config_Option_Array_Interface
{
    protected $_name = 'location_geozone';

    /**
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        //before save
        $row->modified_on = Axis_Date::now()->toSQLString();
        if (empty($row->created_on)) {
            $row->created_on = $row->modified_on;
        }
        
        $row->save();
        return $row;
    }

    /**
     * Return Geozone ids by country & zone
     *
     * @param int $countyrId
     * @param int $zoneId [optional]
     * @return int
     */
    public function getIds($countryId, $zoneId = 0)
    {
        // IF $countryId == 0 => ALL COUNTRY (in country table row with id 0)
        // IF $zoneId == 0 => ALL country zones
        $collect = $this->select()
            ->from('location_geozone', 'id')
            ->joinLeft('location_geozone_zone', 'lg.id = lgz.geozone_id')
            ->where('lgz.country_id IN (0, ?)', $countryId)
            ->order('lg.priority DESC')
            ;
        //add filter zone
        if ($zoneId) {
            $collect->where('lgz.zone_id IN (0, ?)', $zoneId);
        } else {
            $collect->where('lgz.zone_id = 0');
        }
        return $collect->fetchCol();
    }
    
        /**
     *
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        return Axis::single('location/geozone')
            ->select(array('id', 'name'))
            ->fetchPairs();
    }

    /**
     *
     * @static
     * @param int $id
     * @return string
     */
    public static function getConfigOptionName($id)
    {
        if (!$id) return '';
        return Axis::single('location/geozone')->getNameById($id);
    }
}