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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'home' => array(
                'label'         => 'Home',
                'order'         => 10,
                'translator'    => 'Axis_Core',
                'module'        => 'Axis_Admin',
                'controller'    => 'index',
                'action'        => 'index',
                'route'         => 'admin'
            ),
            'admin' => array(
                'label'         => 'Administrate',
                'order'         => 110,
                'uri'           => '#',
                'translator'    => 'Axis_Admin',
                'pages' => array(
                    'user' => array(
                        'label'         => 'Admin users',
                        'order'         => 30,
                        'module'        => 'Axis_Admin',
                        'controller'    => 'user',
                        'action'        => 'index',
                        'route'         => 'admin'
                    ),
                    'role' => array(
                        'label'         => 'Roles',
                        'order'         => 40,
                        'module'        => 'Axis_Admin',
                        'controller'    => 'acl-role',
                        'action'        => 'index',
                        'route'         => 'admin'
                    )
                )
            )
        ));
    }
}
