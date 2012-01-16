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
 * @package     Axis_Search
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

$config = array(
    'Axis_Search' => array(
        'package'  => 'Axis_Search',
        'name'     => 'Search',
        'version'  => '0.1.2',
        'required' => 0,
        'events'   => array(
            'catalog_product_save_after' => array(
                'update_search_index' => array(
                    'type'   => 'model',
                    'model'  => 'search/observer',
                    'method' => 'updateSearchIndexOnProductSave'
                )
            ),
            'cms_page_add_success' => array(
                'update_search_index' => array(
                    'type'   => 'model',
                    'model'  => 'search/observer',
                    'method' => 'updateSearchIndexOnCmsPageAddSuccess'
                )
            ),
            'cms_page_update_success' => array(
                'update_search_index' => array(
                    'type'   => 'model',
                    'model'  => 'search/observer',
                    'method' => 'updateSearchIndexOnCmsPageAddSuccess'
                )
            ),
            'admin_box_navigation_prepare' => array(
                'prepare_menu' => array(
                    'type'   => 'model',
                    'model'  => 'search/observer',
                    'method' => 'prepareAdminNavigationBox'
                )
            )
        )
    )
);
