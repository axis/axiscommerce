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
 * @package     Axis_Csv
 * @subpackage  Axis_Csv_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Csv
 * @subpackage  Axis_Csv_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Csv_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'admin' => array(
                'pages' => array(
                    'import/export' => array(
                        'label'         => 'Import/Export',
                        'order'         => 70,
                        'uri'           => '#',
                        'translator'    => 'Axis_Admin',
                        'pages'         => array(
                            'csv/index' => array(
                                'label'         => 'Csv Import/Export',
                                'order'         => 10,
                                'module'        => 'Axis_Csv',
                                'controller'    => 'index',
                                'action'        => 'index',
                                'route'         => 'admin/axis/csv',
                                'resource'      => 'admin/axis/csv/index/index'
                            )
                        )
                    )
                )
            )
        ));
    }
}
