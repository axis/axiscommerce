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
 * @package     Axis_Tag
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */
$router->addRoute('tag', new Axis_Controller_Router_Route(
    'tag/:controller/:action/*',
    array(
        'module' => 'Axis_Tag',
        'controller' => 'index',
        'action' => 'index'
    )
));
$router->addRoute('tag_show-products', new Axis_Controller_Router_Route(
    'tag/show-products',
    array(
        'module' => 'Axis_Tag',
        'controller' => 'index',
        'action' => 'show-products'
    )
));

$router->addRoute('tag_account',
    new Axis_Controller_Router_Route('account/tag/:action/*', array(
        'module' => 'Axis_Tag',
        'controller' => 'account',
        'action' => 'index'
    )),
    'account' // run before 'account' route
);