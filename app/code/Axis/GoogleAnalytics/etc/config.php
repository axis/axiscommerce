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
 * @package     Axis_GoogleAnalytics
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */
$config = array(
    'Axis_GoogleAnalytics' => array(
        'package' => 'Axis_GoogleAnalytics',
        'name' => 'GoogleAnalytics',
        'version' => '0.1.0',
        'required' => 0, 
        'events' => array(
            'sales_order_create_success' => array(
                'ga_set_order' => array(
                    'type'   => 'single',
                    'model'  => 'Axis_GoogleAnalytics_Box_Ga',
                    'method' => 'setOrder'
                )
            )
        )
    )
);