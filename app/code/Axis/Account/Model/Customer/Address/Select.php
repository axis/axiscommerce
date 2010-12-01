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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Customer_Address_Select extends Axis_Db_Table_Select
{
    /**
     *
     * @return  Axis_Account_Model_Customer_Address_Collect Fluent interface
     */
    public function addCountry()
    {
        $this->joinLeft('location_country',
           'lc.id = aca.country_id',
           array(
               'country_name' => 'name',
               'country_iso_code_2' => 'iso_code_2',
               'country_iso_code_3' => 'iso_code_3',
               'country_address_format_id' => 'address_format_id'
           )
        );
        return $this;
    }

    /**
     *
     * @return  Axis_Account_Model_Customer_Address_Collect Fluent interface
     */
    public function addZone()
    {
        $this->joinLeft('location_zone',
            'lz.id = aca.zone_id',
            array(
                'zone_code' => 'code',
                'zone_name' => 'name',
                'zone_country_id' => 'country_id'
            )
        );

        return $this;
    }
}