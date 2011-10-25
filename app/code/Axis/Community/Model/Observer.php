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
 * @subpackage  Axis_Community_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Observer
{
    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'catalog' => array(
                'pages' => array(
                    'community' => array(
                        'label'         => 'Community',
                        'order'         => 40,
                        'uri'           => '#',
                        'translator'    => 'Axis_Community',
                        'pages'         => array(
                            'community/review' => array(
                                'label'         => 'Reviews',
                                'order'         => 10,
                                'module'        => 'Axis_Community',
                                'controller'    => 'review',
                                'action'        => 'index',
                                'route'         => 'admin/axis/community'
                            ),
                            'community/rating' => array(
                                'label'         => 'Review ratings',
                                'order'         => 20,
                                'module'        => 'Axis_Community',
                                'controller'    => 'rating',
                                'action'        => 'index',
                                'route'         => 'admin/axis/community'
                            )
//                            'community/image' => array(
//                                'label'         => 'Image',
//                                'order'         => 30,
//                                'module'        => 'Axis_Community',
//                                'controller'    => 'image',
//                                'action'        => 'index',
//                                'route'         => 'admin/axis/community'
//                            ),
//                            'community/video' => array(
//                                'label'         => 'Video',
//                                'order'         => 40,
//                                'module'        => 'Axis_Community',
//                                'controller'    => 'video',
//                                'action'        => 'index',
//                                'route'         => 'admin/axis/community'
//                            )
                        )
                    )
                )
            )
        ));
    }
}
