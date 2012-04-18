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
 * @package     Axis_Cms
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

$router->addRoute('cms_default', new Axis_Controller_Router_Route_Front(
    'cms/:controller/:action/*',
    array(
        'module'     => 'Axis_Cms',
        'controller' => 'index',
        'action'     => 'index'
    )
));
$router->addRoute('cms_page', new Axis_Controller_Router_Route_Front(
    'page/:page',
    array(
        'module'     => 'Axis_Cms',
        'controller' => 'view',
        'action'     => 'view-page',
        'page'       => 'page'
    )
));
$router->addRoute('cms_category', new Axis_Controller_Router_Route_Front(
    'cat/:cat',
    array(
        'module'     => 'Axis_Cms',
        'controller' => 'view',
        'action'     => 'view-category',
        'cat'        => 'cat'
    )
));
$router->addRoute('cms', new Axis_Controller_Router_Route_Front(
    'pages',
    array(
        'module'     => 'Axis_Cms',
        'controller' => 'index',
        'action'     => 'index'
    )
));

$router->addRoute('admin/axis/cms', new Axis_Controller_Router_Route_Back(
    'cms/:controller/:action/*',
    array(
        'module'     => 'Axis_Cms',
        'controller' => 'index',
        'action'     => 'index'
    )
), 'admin/axis/admin');
