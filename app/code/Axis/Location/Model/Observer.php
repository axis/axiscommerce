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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Axis_Location_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'location' => array(
                'label'         => 'Locations / Taxes',
                'order'         => 70,
                'uri'           => '#',
                'translator'    => 'Axis_Location',
                'pages' => array(
                    'location/country' => array(
                        'label'         => 'Countries',
                        'order'         => 10,
                        'module'        => 'Axis_Location',
                        'controller'    => 'country',
                        'action'        => 'index',
                        'route'         => 'admin/axis/location',
                        'resource'      => 'admin/axis/location/country/index'
                    ),
                    'location/zone' => array(
                        'label'         => 'States / Provinces',
                        'order'         => 20,
                        'module'        => 'Axis_Location',
                        'controller'    => 'zone',
                        'action'        => 'index',
                        'route'         => 'admin/axis/location',
                        'resource'      => 'admin/axis/location/zone/index'
                    ),
                    'location/geozone' => array(
                        'label'         => 'Geozones',
                        'order'         => 30,
                        'module'        => 'Axis_Location',
                        'controller'    => 'geozone',
                        'action'        => 'index',
                        'route'         => 'admin/axis/location',
                        'resource'      => 'admin/axis/location/geozone/index'
                    )
                )
            )
        ));
    }
}
