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
 * @package     Axis_Sitemap
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */
$router->addRoute('sitemap', new Axis_Controller_Router_Route(
    'sitemap/:controller/:action/*',
    array(
        'module' => 'Axis_Sitemap',
        'controller' => 'index',
        'action' => 'get-all-categories'
    )
));

$router->addRoute('sitemap_get-all-products', new Axis_Controller_Router_Route(
    'sitemap/get-all-products',
    array(
        'module' => 'Axis_Sitemap',
        'controller' => 'index',
        'action' => 'get-all-products'
    )
));
$router->addRoute('sitemap_get-all-pages', new Axis_Controller_Router_Route(
    'sitemap/get-all-pages',
    array(
        'module' => 'Axis_Sitemap',
        'controller' => 'index',
        'action' => 'get-all-pages'
    )
));