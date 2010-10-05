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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Model_Geozone extends Axis_Db_Table
{
	protected $_name = 'location_geozone';
	
    /**
     *
     * @param array $rowData
     * @return bool
     */
    public function save($rowData)
    {
        if (isset($rowData['id']) && $row = $this->fetchRow(
                $this->getAdapter()->quoteInto('id = ?', $rowData['id']))) {

            if (empty($rowData['modified_on'])) {
                $rowData['modified_on'] = Axis_Date::now()->toSQLString();
            }
            $row->setFromArray($rowData);
        } else {
            if (empty($rowData['created_on'])) {
                $rowData['created_on'] = Axis_Date::now()->toSQLString();
            }
            $row = $this->createRow($rowData);
        }
        return $row->save();
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
}