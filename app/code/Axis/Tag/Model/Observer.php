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
 * @subpackage  Axis_Tag_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Tag
 * @subpackage  Axis_Tag_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Tag_Model_Observer
{

    /**
     *
     * @param Axis_Account_Box_Navigation $box
     */
    public function prepareAccountNavigationBox(Axis_Account_Box_Navigation $box)
    {
        $box->addItem($box->getView()->href('account/tag', true), 'My Tags', 'link-tags', 60);
    }

    public function prepareAdminNavigationBox(Axis_Admin_Box_Navigation $box)
    {
        $box->addItem(array(
            'catalog' => array(
                'pages' => array(
                    'tags' => array(
                        'label'         => 'Tags',
                        'order'         => 50,
                        'translator'    => 'Axis_Tag',
                        'module'        => 'Axis_Tag',
                        'route'         => 'admin/axis/tag',
                        'resource'      => 'admin/axis/tag'
                    )
                )
            )
        ));
    }
}