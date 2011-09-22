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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Location_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = 'Zones renamed to States / Provinces, Zones Definitions to Geozones';

    public function up()
    {
        Axis::single('admin/acl_resource')
            ->remove('admin/location_zone')

            ->add('admin/location_zone', 'States / Provinces')
            ->add("admin/location_zone/delete")
            ->add("admin/location_zone/index")
            ->add("admin/location_zone/list")
            ->add("admin/location_zone/save")

            ->add('admin/location_geozone', 'Geozones')
            ->add("admin/location_geozone/delete-assigns")
            ->add("admin/location_geozone/delete")
            ->add("admin/location_geozone/get-assign")
            ->add("admin/location_geozone/index")
            ->add("admin/location_geozone/list-assigns")
            ->add("admin/location_geozone/list")
            ->add("admin/location_geozone/save-assign")
            ->add("admin/location_geozone/save");
    }

    public function down()
    {

    }
}