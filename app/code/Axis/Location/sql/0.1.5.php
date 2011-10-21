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

class Axis_Location_Upgrade_0_1_5 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.5';
    protected $_info = '';

    public function up()
    {
        Axis::single('admin/acl_rule')
            ->rename('admin/location_country',        'admin/location/country')
            ->rename('admin/location_country/index',  'admin/location/country/index')
            ->rename('admin/location_country/list',   'admin/location/country/list')
            ->rename('admin/location_country/save',   'admin/location/country/batch-save')
            ->rename('admin/location_country/delete', 'admin/location/country/remove')

            ->rename('admin/location_zone',        'admin/location/zone')
            ->rename('admin/location_zone/index',  'admin/location/zone/index')
            ->rename('admin/location_zone/list',   'admin/location/zone/list')
            ->rename('admin/location_zone/save',   'admin/location/zone/batch-save')
            ->rename('admin/location_zone/delete', 'admin/location/zone/remove')

            ->rename('admin/location_geozone',        'admin/location/geozone')
            ->rename('admin/location_geozone/index',  'admin/location/geozone/index')
            ->rename('admin/location_geozone/list',   'admin/location/geozone/list')
            ->rename('admin/location_geozone/save',   'admin/location/geozone/batch-save')
            ->rename('admin/location_geozone/delete', 'admin/location/geozone/remove')

            ->rename('admin/location_geozone/list-assigns',   'admin/location/geozone-zone/list')
            ->rename('admin/location_geozone/get-assign',     'admin/location/geozone-zone/load')
            ->rename('admin/location_geozone/save-assign',    'admin/location/geozone-zone/save')
            ->rename('admin/location_geozone/delete-assigns', 'admin/location/geozone-zone/remove')
        ;

    }

    public function down()
    {

    }
}