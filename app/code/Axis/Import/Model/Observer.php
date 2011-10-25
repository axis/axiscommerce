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
 * @package     Axis_Import
 * @subpackage  Axis_Import_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Import
 * @subpackage  Axis_Import_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Import_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'admin' => array(
                'pages' => array(
                    'import/export' => array(
                        'pages'         => array(
                            'import' => array(
                                'label'         => 'OsCommerce',
                                'order'         => 20,
                                'module'        => 'Axis_Import',
                                'controller'    => 'index',
                                'action'        => 'index',
                                'route'         => 'admin/axis/import'
                            )
                        )
                    )
                )
            )
        ));
    }
}
