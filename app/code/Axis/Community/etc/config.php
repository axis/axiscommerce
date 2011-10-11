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
 * @package     Axis_Community
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */
$config = array(
    'Axis_Community' => array(
        'package'  => 'Axis_Community',
        'name'     => 'Community',
        'version'  => '0.1.0',
        'required' => 0,
        'depends'  => array(
            'Axis_Account' => '0.1.5'
        ),
        'events'   => array(
            'admin_box_navigation_prepare' => array(
                'prepare_menu' => array(
                    'type'   => 'model',
                    'model'  => 'community/observer',
                    'method' => 'prepareAdminNavigationBox'
                )
            )
        )
    )
);